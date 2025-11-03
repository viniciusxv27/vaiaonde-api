<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Boost;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InfluencerWebController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Stats
        $totalVideos = Video::where('user_id', $user->id)->count();
        $totalViews = (int) Video::where('user_id', $user->id)->sum('views_count');
        
        // Proposals stats
        $totalProposals = Proposal::where('influencer_id', $user->id)->count();
        $pendingProposals = Proposal::where('influencer_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $acceptedProposals = Proposal::where('influencer_id', $user->id)
            ->where('status', 'accepted')
            ->count();
        
        // Calculate earnings for current month
        $monthEarnings = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'proposal_payment')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        // Calculate earnings for previous month for growth comparison
        $lastMonthEarnings = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'proposal_payment')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');
        
        // Calculate growth percentage
        $earningsGrowth = 0;
        if ($lastMonthEarnings > 0) {
            $earningsGrowth = (($monthEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100;
        } elseif ($monthEarnings > 0) {
            $earningsGrowth = 100;
        }
        
        // Recent videos
        $recentVideos = Video::where('user_id', $user->id)
            ->with('place')
            ->latest()
            ->take(5)
            ->get();
        
        // Recent proposals
        $recentProposals = Proposal::where('influencer_id', $user->id)
            ->with(['place', 'place.owner'])
            ->latest()
            ->take(5)
            ->get();

        return view('influencer.dashboard', compact(
            'totalVideos',
            'totalViews',
            'totalProposals',
            'pendingProposals',
            'acceptedProposals',
            'monthEarnings',
            'earningsGrowth',
            'recentVideos',
            'recentProposals'
        ));
    }

    public function profile()
    {
        return view('influencer.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id . '|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14',
            'pix_key' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'cpf' => $request->cpf,
            'pix_key' => $request->pix_key,
            'bio' => $request->bio,
            'instagram_url' => $request->instagram_url,
            'youtube_url' => $request->youtube_url,
            'tiktok_url' => $request->tiktok_url,
            'twitter_url' => $request->twitter_url,
        ];

        // Upload de avatar
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = time() . '_avatar_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
            $path = $avatar->storeAs('avatars', $filename, 'public');
            $data['avatar'] = asset('storage/' . $path);
        }

        $user->update($data);

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Senha atual incorreta');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Senha alterada com sucesso!');
    }

    public function wallet()
    {
        $user = Auth::user();
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('influencer.wallet', compact('transactions'));
    }

    public function withdrawWallet(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'amount' => 'required|numeric|min:10',
            'payment_method' => 'required|in:pix,bank_transfer',
            'pix_key' => 'required_if:payment_method,pix|string',
        ]);

        if ($request->amount > $user->wallet_balance) {
            return back()->with('error', 'Saldo insuficiente');
        }

        DB::beginTransaction();
        try {
            // Create withdrawal transaction
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'description' => 'Solicitação de saque via ' . $request->payment_method,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'pix_key' => $request->pix_key,
            ]);

            // Deduct from wallet
            $user->wallet_balance -= $request->amount;
            $user->save();

            DB::commit();
            return back()->with('success', 'Solicitação de saque enviada! Aguarde aprovação.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao processar saque: ' . $e->getMessage());
        }
    }

    public function proposals(Request $request)
    {
        $user = Auth::user();
        
        $status = $request->get('status', 'all');
        
        $query = Proposal::where('influencer_id', $user->id)
            ->with(['place', 'place.owner', 'place.city']);

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $proposals = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calcular estatísticas
        $pendingCount = Proposal::where('influencer_id', $user->id)->where('status', 'pending')->count();
        $acceptedCount = Proposal::where('influencer_id', $user->id)->where('status', 'accepted')->count();
        $rejectedCount = Proposal::where('influencer_id', $user->id)->where('status', 'rejected')->count();
        $totalEarned = Proposal::where('influencer_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        return view('influencer.proposals', compact('proposals', 'pendingCount', 'acceptedCount', 'rejectedCount', 'totalEarned'));
    }

    public function acceptProposal($id)
    {
        // Influencer NÃO pode aceitar - apenas proprietário pode aceitar
        return back()->with('error', 'Apenas o proprietário pode aceitar propostas.');
    }

    public function rejectProposal($id)
    {
        $user = Auth::user();
        $proposal = Proposal::where('id', $id)
            ->where('influencer_id', $user->id)
            ->firstOrFail();

        $proposal->update(['status' => 'rejected']);

        return back()->with('success', 'Proposta recusada.');
    }

    public function submitProposalForApproval($id)
    {
        $user = Auth::user();
        $proposal = Proposal::where('id', $id)
            ->where('influencer_id', $user->id)
            ->where('status', 'accepted')
            ->firstOrFail();

        $proposal->update(['status' => 'submitted_for_approval']);

        return back()->with('success', 'Proposta enviada para aprovação do proprietário!');
    }

    public function getProposalsByPlace($placeId)
    {
        $user = Auth::user();
        
        $proposals = Proposal::where('influencer_id', $user->id)
            ->where('place_id', $placeId)
            ->whereIn('status', ['accepted', 'submitted_for_approval'])
            ->get()
            ->map(function($proposal) {
                return [
                    'id' => $proposal->id,
                    'title' => $proposal->title,
                    'amount' => $proposal->amount,
                    'amount_formatted' => number_format($proposal->amount, 2, ',', '.'),
                    'status' => $proposal->status,
                ];
            });

        return response()->json(['proposals' => $proposals]);
    }

    public function sendProposal(Request $request, $chatId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'payment_amount' => 'required|numeric|min:0',
            'delivery_date' => 'nullable|date',
        ]);

        $user = Auth::user();
        $chat = Chat::where('id', $chatId)
            ->where('influencer_id', $user->id)
            ->with('place')
            ->firstOrFail();

        $proposal = Proposal::create([
            'place_id' => $chat->place_id,
            'influencer_id' => $user->id,
            'title' => 'Proposta de Divulgação - ' . $chat->place->name,
            'description' => $request->message,
            'amount' => $request->payment_amount,
            'deadline_days' => $request->delivery_date ? now()->diffInDays($request->delivery_date) : 7,
            'status' => 'pending',
        ]);

        // Enviar mensagem no chat informando sobre a proposta
        Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'message' => '📋 Proposta enviada: ' . $request->message . ' | Valor: R$ ' . number_format($request->payment_amount, 2, ',', '.'),
            'type' => 'proposal',
            'read' => false,
        ]);

        return back()->with('success', 'Proposta enviada com sucesso!');
    }

    public function deleteProposal($id)
    {
        $user = Auth::user();
        $proposal = Proposal::where('id', $id)
            ->where('influencer_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $proposal->delete();

        return back()->with('success', 'Proposta excluída com sucesso!');
    }

    public function chats()
    {
        $user = Auth::user();
        
        $conversations = Chat::where('influencer_id', $user->id)
            ->with(['place', 'place.owner'])
            ->get()
            ->map(function($chat) use ($user) {
                $lastMessage = $chat->messages()->latest()->first();
                return (object)[
                    'id' => $chat->id,
                    'partner_name' => $chat->place->name ?? 'Estabelecimento',
                    'last_message' => $lastMessage ? $lastMessage->message : 'Sem mensagens',
                    'last_message_at' => $lastMessage ? $lastMessage->created_at : $chat->created_at,
                    'unread_count' => $chat->messages()->where('sender_id', '!=', $user->id)->where('read', false)->count(),
                ];
            });

        return view('influencer.chats', compact('conversations'));
    }

    public function showChat($id)
    {
        $user = Auth::user();
        $chat = Chat::where('id', $id)
            ->where('influencer_id', $user->id)
            ->with(['place', 'place.owner', 'messages.sender'])
            ->firstOrFail();

        // Mark messages as read
        Message::where('chat_id', $chat->id)
            ->where('sender_id', '!=', $user->id)
            ->where('read', false)
            ->update(['read' => true]);

        $activeChat = (object)[
            'id' => $chat->id,
            'partner_name' => $chat->place->name ?? 'Estabelecimento',
            'place_name' => $chat->place->name ?? 'Estabelecimento',
        ];

        $messages = $chat->messages()->with('sender')->orderBy('created_at')->get();
        
        $conversations = Chat::where('influencer_id', $user->id)
            ->with(['place', 'place.owner'])
            ->get()
            ->map(function($c) use ($user) {
                $lastMessage = $c->messages()->latest()->first();
                return (object)[
                    'id' => $c->id,
                    'partner_name' => $c->place->name ?? 'Estabelecimento',
                    'last_message' => $lastMessage ? $lastMessage->message : 'Sem mensagens',
                    'last_message_at' => $lastMessage ? $lastMessage->created_at : $c->created_at,
                    'unread_count' => $c->messages()->where('sender_id', '!=', $user->id)->where('read', false)->count(),
                ];
            });

        return view('influencer.chats', compact('activeChat', 'messages', 'conversations'));
    }

    public function getPlaces()
    {
        try {
            $places = Place::select('id', 'name', 'city_id', 'categories_ids', 'card_image')
                ->where('is_active', 1)
                ->get()
                ->map(function($place) {
                    $city = \App\Models\City::find($place->city_id);
                    
                    // Pega a primeira categoria se houver
                    $categoryName = 'Sem categoria';
                    if ($place->categories_ids) {
                        $categoryIds = explode(',', $place->categories_ids);
                        if (!empty($categoryIds[0])) {
                            $category = \App\Models\Categorie::find($categoryIds[0]);
                            $categoryName = $category->name ?? 'Sem categoria';
                        }
                    }
                    
                    $videosCount = \App\Models\Video::where('place_id', $place->id)->count();
                    $images = \App\Models\PlaceImage::where('place_id', $place->id)->first();
                    
                    return [
                        'id' => $place->id,
                        'name' => $place->name,
                        'city' => $city->name ?? 'Não informado',
                        'category' => $categoryName,
                        'image' => $images->image_url ?? $place->card_image ?? null,
                        'videos_count' => $videosCount,
                    ];
                });

            return response()->json(['places' => $places]);
        } catch (\Exception $e) {
            \Log::error('Error getting places: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['places' => [], 'error' => $e->getMessage()]);
        }
    }

    public function startChatWithPlace(Request $request)
    {
        $request->validate([
            'place_id' => 'required|exists:place,id',
        ]);

        $user = Auth::user();

        // Verifica se já existe um chat
        $existingChat = Chat::where('place_id', $request->place_id)
            ->where('influencer_id', $user->id)
            ->first();

        if ($existingChat) {
            return response()->json(['success' => true, 'chat_id' => $existingChat->id]);
        }

        // Cria novo chat
        $chat = Chat::create([
            'place_id' => $request->place_id,
            'influencer_id' => $user->id,
        ]);

        return response()->json(['success' => true, 'chat_id' => $chat->id]);
    }

    public function sendMessage(Request $request, $id)
    {
        $user = Auth::user();
        $chat = Chat::where('id', $id)
            ->where('influencer_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'type' => 'text',
            'read' => false,
        ]);

        return back()->with('success', 'Mensagem enviada!');
    }

    public function videos()
    {
        $user = Auth::user();
        $videos = Video::where('user_id', $user->id)
            ->with(['place', 'user', 'activeBoost'])
            ->latest()
            ->paginate(12);

        // Stats para o header (garantindo que são números)
        $totalVideos = (int) Video::where('user_id', $user->id)->count();
        $totalViews = (int) Video::where('user_id', $user->id)->sum('views_count');
        $totalEarnings = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'video_payment')
            ->sum('amount');

        // Propostas aceitas para vincular aos vídeos
        $acceptedProposals = Proposal::where('influencer_id', $user->id)
            ->where('status', 'accepted')
            ->with('place')
            ->get();

        return view('influencer.videos', compact('videos', 'totalVideos', 'totalViews', 'totalEarnings', 'acceptedProposals'));
    }

    public function storeVideo(Request $request)
    {
        \Log::info('=== INÍCIO DO UPLOAD DE VÍDEO ===');
        
        // Log dos dados brutos do PHP
        \Log::info('PHP $_FILES', ['files' => $_FILES]);
        \Log::info('PHP $_POST', ['post' => $_POST]);
        \Log::info('PHP Configs', [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'content_length' => $request->header('Content-Length')
        ]);
        
        \Log::info('Dados do request:', [
            'has_video' => $request->hasFile('video'),
            'video_size' => $request->hasFile('video') ? $request->file('video')->getSize() : 0,
            'video_valid' => $request->hasFile('video') ? $request->file('video')->isValid() : false,
            'video_error' => $request->hasFile('video') ? $request->file('video')->getError() : 'no file',
            'title' => $request->input('title'),
            'all_files' => array_keys($request->allFiles()),
            'content_type' => $request->header('Content-Type'),
        ]);

        try {
            $request->validate([
                'place_id' => 'nullable|exists:place,id',
                'proposal_id' => 'nullable|exists:proposals,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:20480', // 20MB (temporário para testar)
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB
                'status' => 'nullable|in:published,draft',
            ], [
                'video.required' => 'Você deve selecionar um arquivo de vídeo',
                'video.mimes' => 'O vídeo deve ser nos formatos: MP4, MOV, AVI ou WMV',
                'video.max' => 'O vídeo não pode ser maior que 20MB (temporário - configure Apache/Nginx para vídeos maiores)',
                'video.file' => 'O arquivo de vídeo é inválido',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erro de validação', [
                'errors' => $e->errors(),
                'has_video' => $request->hasFile('video'),
            ]);
            
            // Se for AJAX, retornar JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->errors()['video'][0] ?? 'Erro de validação',
                    'errors' => $e->errors()
                ], 422);
            }
            
            throw $e;
        }

        \Log::info('Validação passou com sucesso');

        $user = Auth::user();

        try {
            DB::beginTransaction();
            \Log::info('Transaction iniciada');

            // Verificar se o vídeo foi enviado
            if (!$request->hasFile('video')) {
                throw new \Exception('Nenhum arquivo de vídeo foi enviado');
            }

            if (!$request->file('video')->isValid()) {
                throw new \Exception('Arquivo de vídeo inválido ou corrompido');
            }

            $video = $request->file('video');
            $thumbnail = $request->file('thumbnail');

            \Log::info('Arquivos validados, iniciando upload');

            // Upload do vídeo - tenta R2 primeiro, depois local como fallback
            $videoFilename = time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
            $videoPath = 'videos/' . $videoFilename;
            $videoUrl = null;
            
            \Log::info('Tentando fazer upload do vídeo', ['path' => $videoPath, 'size' => $video->getSize()]);
            
            // Verificar se R2 está configurado E se o pacote AWS S3 está instalado
            $r2Configured = false;
            
            $r2AccessKey = env('R2_ACCESS_KEY_ID');
            $r2SecretKey = env('R2_SECRET_ACCESS_KEY');
            $r2Bucket = env('R2_BUCKET');
            $r2PublicUrl = env('R2_PUBLIC_URL');
            
            \Log::info('Verificando configuração R2', [
                'has_access_key' => !empty($r2AccessKey),
                'has_secret_key' => !empty($r2SecretKey),
                'has_bucket' => !empty($r2Bucket),
                'has_public_url' => !empty($r2PublicUrl),
                'class_exists' => class_exists('League\Flysystem\AwsS3V3\AwsS3V3Adapter')
            ]);
            
            if ($r2AccessKey && $r2SecretKey && $r2Bucket) {
                // Verificar se a classe necessária existe
                if (class_exists('League\Flysystem\AwsS3V3\AwsS3V3Adapter')) {
                    $r2Configured = true;
                    \Log::info('R2/S3 configurado e pacote instalado - Upload para nuvem será usado');
                } else {
                    \Log::warning('R2 configurado mas pacote AWS S3 não instalado. Use: composer require league/flysystem-aws-s3-v3');
                }
            } else {
                \Log::info('R2 não configurado completamente - usando armazenamento local', [
                    'missing_vars' => array_filter([
                        'R2_ACCESS_KEY_ID' => empty($r2AccessKey) ? 'faltando' : null,
                        'R2_SECRET_ACCESS_KEY' => empty($r2SecretKey) ? 'faltando' : null,
                        'R2_BUCKET' => empty($r2Bucket) ? 'faltando' : null,
                    ])
                ]);
            }
            
            if ($r2Configured) {
                try {
                    \Log::info('Tentando upload para R2/Cloudflare');
                    
                    // Usar putFileAs ao invés de put com file_get_contents para economizar memória
                    $uploadedPath = Storage::disk('r2')->putFileAs('videos', $video, $videoFilename, 'public');
                    $videoUrl = env('R2_PUBLIC_URL') . '/' . $uploadedPath;
                    
                    \Log::info('Upload do vídeo para R2 concluído', ['url' => $videoUrl]);
                } catch (\Exception $r2Error) {
                    \Log::warning('Falha no upload para R2, tentando armazenamento local', [
                        'error' => $r2Error->getMessage(),
                        'file' => $r2Error->getFile(),
                        'line' => $r2Error->getLine()
                    ]);
                    $r2Configured = false; // Força fallback para local
                }
            }
            
            // Fallback: upload local se R2 não estiver configurado ou falhar
            if (!$r2Configured || !$videoUrl) {
                \Log::info('Usando armazenamento local como fallback');
                
                // Garantir que o diretório existe
                $uploadDir = public_path('uploads/videos');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                    \Log::info('Diretório de vídeos criado', ['path' => $uploadDir]);
                }
                
                // Salvar arquivo diretamente (retorna apenas o nome do arquivo)
                $video->move($uploadDir, $videoFilename);
                
                // Construir URL correta (sem duplicação)
                $appUrl = rtrim(env('APP_URL'), '/');
                $videoUrl = $appUrl . '/uploads/videos/' . $videoFilename;
                
                \Log::info('Upload do vídeo para disco local concluído', [
                    'path' => $uploadDir . '/' . $videoFilename,
                    'url' => $videoUrl
                ]);
            }

            // Upload da thumbnail - mesma lógica de fallback
            $thumbnailUrl = null;
            if ($thumbnail) {
                $thumbnailFilename = time() . '_thumb_' . uniqid() . '.' . $thumbnail->getClientOriginalExtension();
                
                \Log::info('Tentando fazer upload da thumbnail');
                
                // Usar mesma lógica: R2 se disponível, senão local
                $thumbnailUploaded = false;
                
                if ($r2Configured && class_exists('League\Flysystem\AwsS3V3\AwsS3V3Adapter')) {
                    try {
                        $uploadedThumbPath = Storage::disk('r2')->putFileAs('thumbnails', $thumbnail, $thumbnailFilename, 'public');
                        $thumbnailUrl = env('R2_PUBLIC_URL') . '/' . $uploadedThumbPath;
                        $thumbnailUploaded = true;
                        \Log::info('Upload da thumbnail para R2 concluído', ['url' => $thumbnailUrl]);
                    } catch (\Exception $thumbError) {
                        \Log::warning('Falha no upload da thumbnail para R2, usando local', [
                            'error' => $thumbError->getMessage()
                        ]);
                    }
                }
                
                // Fallback local para thumbnail
                if (!$thumbnailUploaded) {
                    $thumbDir = public_path('uploads/thumbnails');
                    if (!file_exists($thumbDir)) {
                        mkdir($thumbDir, 0755, true);
                    }
                    
                    // Salvar arquivo diretamente
                    $thumbnail->move($thumbDir, $thumbnailFilename);
                    
                    // Construir URL correta
                    $appUrl = rtrim(env('APP_URL'), '/');
                    $thumbnailUrl = $appUrl . '/uploads/thumbnails/' . $thumbnailFilename;
                    
                    \Log::info('Upload da thumbnail para disco local concluído', [
                        'path' => $thumbDir . '/' . $thumbnailFilename,
                        'url' => $thumbnailUrl
                    ]);
                }
            }

            // Determinar se está ativo baseado no status
            $isActive = ($request->status ?? 'published') === 'published';

            // Se tiver proposal_id, puxar place_id automaticamente
            $placeId = $request->place_id;
            if ($request->proposal_id) {
                $proposal = Proposal::find($request->proposal_id);
                if ($proposal) {
                    $placeId = $proposal->place_id;
                }
            }

            // Criar vídeo
            $videoRecord = Video::create([
                'user_id' => $user->id,
                'place_id' => $placeId,
                'proposal_id' => $request->proposal_id,
                'title' => $request->title,
                'description' => $request->description,
                'video_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'active' => $isActive,
                'views_count' => 0,
                'likes_count' => 0,
                'shares_count' => 0,
            ]);

            \Log::info('Vídeo criado no banco de dados', [
                'id' => $videoRecord->id,
                'video_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'storage_type' => $r2Configured ? 'R2/Cloudflare' : 'Local'
            ]);

            DB::commit();
            \Log::info('=== UPLOAD CONCLUÍDO COM SUCESSO ===');
            
            // Se for AJAX, retornar JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vídeo enviado com sucesso!',
                    'video' => [
                        'id' => $videoRecord->id,
                        'title' => $videoRecord->title,
                        'url' => $videoRecord->video_url
                    ]
                ]);
            }
            
            return redirect()->route('influencer.videos.index')->with('success', 'Vídeo enviado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Erro de validação', ['errors' => $e->errors()]);
            
            // Se for AJAX, retornar JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $e->errors()
                ], 422);
            }
            
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('=== ERRO AO ENVIAR VÍDEO ===');
            \Log::error('Mensagem: ' . $e->getMessage());
            \Log::error('Arquivo: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            // Identificar tipo de erro específico
            $errorMessage = 'Erro ao fazer upload: ';
            
            if (strpos($e->getMessage(), 'The file') !== false && strpos($e->getMessage(), 'was not uploaded') !== false) {
                $errorMessage .= 'Arquivo não foi enviado corretamente. Verifique o tamanho do arquivo e a configuração do servidor.';
            } elseif (strpos($e->getMessage(), 'size exceeds') !== false || strpos($e->getMessage(), 'upload_max_filesize') !== false) {
                $errorMessage .= 'Arquivo muito grande. Máximo permitido: ' . ini_get('upload_max_filesize');
            } elseif (strpos($e->getMessage(), 'disk') !== false || strpos($e->getMessage(), 'storage') !== false) {
                $errorMessage .= 'Erro ao salvar arquivo no servidor. Verifique permissões de escrita.';
            } elseif (strpos($e->getMessage(), 'S3') !== false || strpos($e->getMessage(), 'R2') !== false || strpos($e->getMessage(), 'Cloudflare') !== false) {
                $errorMessage .= 'Erro na conexão com R2/Cloudflare. Verificando credenciais...';
            } elseif (strpos($e->getMessage(), 'cURL') !== false || strpos($e->getMessage(), 'Connection') !== false) {
                $errorMessage .= 'Erro de conexão com o servidor de armazenamento. Tente novamente.';
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            \Log::error('Mensagem formatada para usuário: ' . $errorMessage);
            
            // Se for AJAX, retornar JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'debug' => config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null
                ], 500);
            }
            
            return back()->withErrors(['video' => $errorMessage])->withInput();
        }
    }

    public function editVideo($id)
    {
        $user = Auth::user();
        $video = Video::where('id', $id)
            ->where('user_id', $user->id)
            ->with('place')
            ->firstOrFail();

        $places = \App\Models\Place::select('id', 'name')->get();
        
        // Get proposals for the current place if exists
        $proposals = [];
        if ($video->place_id) {
            $proposals = Proposal::where('influencer_id', $user->id)
                ->where('place_id', $video->place_id)
                ->whereIn('status', ['accepted', 'submitted_for_approval'])
                ->get();
        }

        return view('influencer.videos-edit', compact('video', 'places', 'proposals'));
    }

    public function updateVideo(Request $request, $id)
    {
        $user = Auth::user();
        $video = Video::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'place_id' => 'nullable|exists:place,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:512000', // 500MB
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB
            'status' => 'nullable|in:published,draft',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'place_id' => $request->place_id,
                'proposal_id' => $request->proposal_id,
                'title' => $request->title,
                'description' => $request->description,
                'active' => ($request->status ?? 'published') === 'published',
            ];
            
            // Se tiver proposal_id, puxar o place_id automaticamente
            if ($request->proposal_id) {
                $proposal = Proposal::find($request->proposal_id);
                if ($proposal) {
                    $data['place_id'] = $proposal->place_id;
                }
            }

            // Se enviou novo vídeo, deletar o antigo e fazer upload do novo
            if ($request->hasFile('video') && $request->file('video')->isValid()) {
                // Deletar vídeo antigo do R2
                if ($video->video_url) {
                    $oldVideoPath = parse_url($video->video_url, PHP_URL_PATH);
                    if ($oldVideoPath) {
                        $oldVideoPath = ltrim($oldVideoPath, '/');
                        Storage::disk('r2')->delete($oldVideoPath);
                    }
                }

                // Upload do novo vídeo
                $newVideo = $request->file('video');
                $videoFilename = time() . '_' . uniqid() . '.' . $newVideo->getClientOriginalExtension();
                $videoPath = 'videos/' . $videoFilename;
                
                Storage::disk('r2')->put($videoPath, file_get_contents($newVideo), 'public');
                $data['video_url'] = env('R2_PUBLIC_URL') . '/' . $videoPath;
            }

            // Se enviou nova thumbnail, deletar a antiga e fazer upload da nova
            if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
                // Deletar thumbnail antiga do R2
                if ($video->thumbnail_url) {
                    $oldThumbPath = parse_url($video->thumbnail_url, PHP_URL_PATH);
                    if ($oldThumbPath) {
                        $oldThumbPath = ltrim($oldThumbPath, '/');
                        Storage::disk('r2')->delete($oldThumbPath);
                    }
                }

                // Upload da nova thumbnail
                $newThumb = $request->file('thumbnail');
                $thumbnailFilename = time() . '_thumb_' . uniqid() . '.' . $newThumb->getClientOriginalExtension();
                $thumbnailPath = 'thumbnails/' . $thumbnailFilename;
                
                Storage::disk('r2')->put($thumbnailPath, file_get_contents($newThumb), 'public');
                $data['thumbnail_url'] = env('R2_PUBLIC_URL') . '/' . $thumbnailPath;
            }

            $video->update($data);

            DB::commit();
            return redirect()->route('influencer.videos.index')->with('success', 'Vídeo atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao atualizar vídeo: ' . $e->getMessage());
            return back()->withErrors(['video' => 'Erro ao atualizar: ' . $e->getMessage()])->withInput();
        }
    }

    public function deleteVideo($id)
    {
        $user = Auth::user();
        $video = Video::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        
        try {
            DB::beginTransaction();

            // Extrair o caminho do arquivo da URL do R2
            $videoPath = null;
            $thumbnailPath = null;

            if ($video->video_url) {
                // Extrair o caminho após o domínio
                $videoPath = parse_url($video->video_url, PHP_URL_PATH);
                $videoPath = ltrim($videoPath, '/'); // Remove a barra inicial
            }

            if ($video->thumbnail_url) {
                $thumbnailPath = parse_url($video->thumbnail_url, PHP_URL_PATH);
                $thumbnailPath = ltrim($thumbnailPath, '/');
            }

            // Excluir do Cloudflare R2
            if ($videoPath && Storage::disk('r2')->exists($videoPath)) {
                Storage::disk('r2')->delete($videoPath);
                \Log::info('Vídeo excluído do R2', ['path' => $videoPath]);
            }

            if ($thumbnailPath && Storage::disk('r2')->exists($thumbnailPath)) {
                Storage::disk('r2')->delete($thumbnailPath);
                \Log::info('Thumbnail excluída do R2', ['path' => $thumbnailPath]);
            }

            // Excluir do banco de dados
            $video->delete();

            DB::commit();
            
            return back()->with('success', 'Vídeo excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao excluir vídeo', [
                'video_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erro ao excluir vídeo: ' . $e->getMessage());
        }
    }

    public function depositWallet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'payment_method' => 'required|in:card,pix',
        ]);

        $user = Auth::user();

        try {
            // Depósito via Cartão
            if ($request->payment_method === 'card') {
                $request->validate([
                    'stripeToken' => 'required|string',
                ]);

                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

                $transaction = \App\Models\Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'amount' => $request->amount,
                    'balance_before' => $user->wallet_balance,
                    'balance_after' => $user->wallet_balance + $request->amount,
                    'description' => 'Recarga de saldo via cartão - Influenciador',
                    'payment_method' => 'card',
                    'status' => 'pending',
                ]);

                try {
                    $charge = \Stripe\Charge::create([
                        'amount' => $request->amount * 100, // Stripe usa centavos
                        'currency' => 'brl',
                        'source' => $request->stripeToken,
                        'description' => "Recarga saldo - Influenciador - User {$user->id}",
                        'metadata' => [
                            'user_id' => $user->id,
                            'transaction_id' => $transaction->id,
                            'type' => 'influencer_deposit'
                        ]
                    ]);

                    if ($charge->status === 'succeeded') {
                        $user->increment('wallet_balance', $request->amount);

                        $transaction->update([
                            'status' => 'completed',
                            'stripe_charge_id' => $charge->id,
                            'balance_after' => $user->wallet_balance,
                        ]);

                        return back()->with('success', 'Saldo adicionado com sucesso via cartão! Novo saldo: R$ ' . number_format($user->wallet_balance, 2, ',', '.'));
                    } else {
                        $transaction->update(['status' => 'failed']);
                        return back()->with('error', 'Pagamento não autorizado. Tente novamente.');
                    }

                } catch (\Stripe\Exception\CardException $e) {
                    $transaction->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
                    
                    return back()->with('error', 'Erro no cartão: ' . $e->getError()->message);
                    
                } catch (\Exception $e) {
                    $transaction->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
                    
                    return back()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
                }
            }
            
            // Depósito via PIX
            elseif ($request->payment_method === 'pix') {
                $abacatePayService = new \App\Services\AbacatePayService();
                
                $result = $abacatePayService->createPixQrCode(
                    $request->amount,
                    'Recarga de saldo - Influenciador - User: ' . $user->id,
                    [
                        'name' => $user->name,
                        'cellphone' => $user->phone ?? '',
                        'email' => $user->email,
                        'taxId' => $user->cpf ?? ''
                    ],
                    [
                        'user_id' => $user->id,
                        'type' => 'influencer_deposit'
                    ]
                );

                if (!$result['success']) {
                    return back()->with('error', $result['error'] ?? 'Erro ao gerar PIX');
                }

                if (empty($result['id']) || empty($result['qr_code'])) {
                    return back()->with('error', 'Erro ao gerar QR Code PIX. Tente novamente.');
                }

                return back()->with([
                    'success' => 'QR Code PIX gerado com sucesso! Aguardando pagamento...',
                    'pix_qr_code' => $result['qr_code'],
                    'pix_qr_code_url' => $result['qr_code_url'],
                    'pix_id' => $result['id'],
                    'pix_amount' => $request->amount,
                    'pix_expires_at' => $result['expires_at'],
                    'show_pix_modal' => true
                ]);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar depósito: ' . $e->getMessage());
        }
    }

    public function checkPixPayment(Request $request)
    {
        $request->validate([
            'pix_id' => 'required|string',
            'amount' => 'required|numeric'
        ]);

        try {
            $abacatePayService = new \App\Services\AbacatePayService();
            $result = $abacatePayService->checkPixQrCode($request->pix_id);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Erro ao verificar pagamento'
                ]);
            }

            if ($result['paid']) {
                $user = Auth::user();
                $amount = $request->amount;

                // Evitar duplicação
                $existingTransaction = Transaction::where('user_id', $user->id)
                    ->where('description', 'LIKE', '%PIX%' . $request->pix_id . '%')
                    ->where('type', 'deposit')
                    ->first();

                if (!$existingTransaction) {
                    $user->increment('wallet_balance', $amount);

                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'deposit',
                        'amount' => $amount,
                        'balance_before' => $user->wallet_balance - $amount,
                        'balance_after' => $user->wallet_balance,
                        'description' => 'Depósito via PIX - ID: ' . $request->pix_id,
                        'payment_method' => 'pix',
                        'status' => 'completed'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'paid' => true,
                    'message' => 'Pagamento confirmado! Saldo creditado.'
                ]);
            }

            return response()->json([
                'success' => true,
                'paid' => false,
                'message' => 'Aguardando pagamento...'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro ao verificar pagamento: ' . $e->getMessage()
            ]);
        }
    }

    public function boostVideo(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:videos,id',
            'amount' => 'required|numeric|min:10',
            'days' => 'required|integer|min:1|max:30',
        ]);

        $user = Auth::user();
        $video = Video::findOrFail($request->video_id);

        // Verificar se o vídeo pertence ao influenciador
        if ($video->user_id !== $user->id) {
            return back()->with('error', 'Você não tem permissão para impulsionar este vídeo');
        }

        // Verificar saldo
        if ($user->wallet_balance < $request->amount) {
            return back()->with('error', 'Saldo insuficiente');
        }

        DB::beginTransaction();
        try {
            $amount = (float) $request->amount;
            $days = (int) $request->days;
            $dailyBudget = $amount / $days;

            // Criar impulsionamento
            $boost = Boost::create([
                'video_id' => $request->video_id,
                'user_id' => $user->id,
                'amount' => $amount,
                'days' => $days,
                'daily_budget' => $dailyBudget,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays($days),
            ]);

            // Debitar saldo
            $user->decrement('wallet_balance', $amount);

            // Registrar transação
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'boost_payment',
                'amount' => -$amount,
                'balance_before' => $user->wallet_balance + $amount,
                'balance_after' => $user->wallet_balance,
                'description' => "Impulsionamento de vídeo por {$days} dias - Budget diário: R$ " . number_format($dailyBudget, 2, ',', '.'),
                'status' => 'completed'
            ]);

            DB::commit();
            return back()->with('success', 'Vídeo impulsionado com sucesso! Budget diário: R$ ' . number_format($dailyBudget, 2, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao impulsionar vídeo: ' . $e->getMessage());
        }
    }

    public function finalizeBoost($id)
    {
        $user = Auth::user();
        $boost = Boost::findOrFail($id);

        // Verificar se o boost pertence ao usuário
        if ($boost->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para modificar este impulsionamento'
            ], 403);
        }

        // Verificar se não está já finalizado
        if ($boost->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Este impulsionamento já foi finalizado'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Calcular o saldo restante
            $daysElapsed = $boost->start_date->diffInDays(now());
            if ($daysElapsed > $boost->days) {
                $daysElapsed = $boost->days;
            }
            
            $spent = $daysElapsed * $boost->daily_budget;
            $remaining = $boost->amount - $spent;
            
            // Se houver saldo restante, devolver ao usuário
            if ($remaining > 0) {
                $user->increment('wallet_balance', $remaining);
                
                // Registrar transação de reembolso
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'proposal_refund',
                    'amount' => $remaining,
                    'balance_before' => $user->wallet_balance - $remaining,
                    'balance_after' => $user->wallet_balance,
                    'description' => "Reembolso de impulsionamento finalizado - Saldo restante",
                    'status' => 'completed'
                ]);
            }

            // Atualizar status do boost
            $boost->status = 'completed';
            $boost->end_date = now(); // Atualizar data de término para agora
            $boost->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $remaining > 0 
                    ? 'Campanha finalizada! R$ ' . number_format($remaining, 2, ',', '.') . ' foi devolvido à sua carteira.'
                    : 'Campanha finalizada com sucesso!',
                'refunded' => $remaining,
                'status' => 'completed'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar campanha: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleBoost($id)
    {
        $user = Auth::user();
        $boost = Boost::findOrFail($id);

        // Verificar se o boost pertence ao usuário
        if ($boost->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para modificar este impulsionamento'
            ], 403);
        }

        try {
            $newStatus = $boost->status === 'active' ? 'paused' : 'active';
            $boost->status = $newStatus;
            $boost->save();

            $message = $newStatus === 'paused' 
                ? 'Campanha pausada com sucesso' 
                : 'Campanha retomada com sucesso';

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao modificar campanha: ' . $e->getMessage()
            ], 500);
        }
    }
}
