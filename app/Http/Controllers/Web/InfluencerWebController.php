<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Boost;
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
            ->with('place')
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

    public function proposals()
    {
        $user = Auth::user();
        $proposals = Proposal::where('influencer_id', $user->id)
            ->with(['place', 'place.owner'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('influencer.proposals', compact('proposals'));
    }

    public function acceptProposal($id)
    {
        $user = Auth::user();
        $proposal = Proposal::where('id', $id)
            ->where('influencer_id', $user->id)
            ->firstOrFail();

        $proposal->update(['status' => 'accepted']);

        return back()->with('success', 'Proposta aceita com sucesso!');
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

    public function chats()
    {
        $user = Auth::user();
        $chats = Chat::where('influencer_id', $user->id)
            ->with(['place', 'place.owner', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->get();

        return view('influencer.chats', compact('chats'));
    }

    public function showChat($id)
    {
        $user = Auth::user();
        $chat = Chat::where('id', $id)
            ->where('influencer_id', $user->id)
            ->with(['place', 'place.owner', 'messages.user'])
            ->firstOrFail();

        // Mark messages as read
        Message::where('chat_id', $chat->id)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('influencer.chat-show', compact('chat'));
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
            'user_id' => $user->id,
            'message' => $request->message,
            'is_read' => false,
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

            \Log::info('Arquivos validados, iniciando upload para R2');

            // Upload do vídeo para R2
            $videoFilename = time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
            $videoPath = 'videos/' . $videoFilename;
            
            \Log::info('Tentando fazer upload do vídeo', ['path' => $videoPath, 'size' => $video->getSize()]);
            
            // Usar putFileAs ao invés de put com file_get_contents para economizar memória
            $uploadedPath = Storage::disk('r2')->putFileAs('videos', $video, $videoFilename, 'public');
            $videoUrl = env('R2_PUBLIC_URL') . '/' . $uploadedPath;
            
            \Log::info('Upload do vídeo concluído', ['url' => $videoUrl]);

            // Upload da thumbnail para R2
            $thumbnailUrl = null;
            if ($thumbnail) {
                $thumbnailFilename = time() . '_thumb_' . uniqid() . '.' . $thumbnail->getClientOriginalExtension();
                
                \Log::info('Tentando fazer upload da thumbnail');
                
                $uploadedThumbPath = Storage::disk('r2')->putFileAs('thumbnails', $thumbnail, $thumbnailFilename, 'public');
                $thumbnailUrl = env('R2_PUBLIC_URL') . '/' . $uploadedThumbPath;
                
                \Log::info('Upload da thumbnail concluído', ['url' => $thumbnailUrl]);
            }

            // Determinar se está ativo baseado no status
            $isActive = ($request->status ?? 'published') === 'published';

            // Criar vídeo
            $videoRecord = Video::create([
                'user_id' => $user->id,
                'place_id' => $request->place_id,
                'title' => $request->title,
                'description' => $request->description,
                'video_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'active' => $isActive,
                'views_count' => 0,
                'likes_count' => 0,
                'shares_count' => 0,
            ]);

            \Log::info('Vídeo criado no banco de dados', ['id' => $videoRecord->id]);

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
            
            // Se for AJAX, retornar JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao fazer upload: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['video' => 'Erro ao fazer upload: ' . $e->getMessage()])->withInput();
        }
    }

    public function editVideo($id)
    {
        $user = Auth::user();
        $video = Video::where('id', $id)
            ->where('user_id', $user->id)
            ->with('place')
            ->firstOrFail();

        $places = \App\Models\Place::where('owner_id', $user->id)->get();

        return view('influencer.videos-edit', compact('video', 'places'));
    }

    public function updateVideo(Request $request, $id)
    {
        $user = Auth::user();
        $video = Video::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'place_id' => 'nullable|exists:place,id',
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
                'title' => $request->title,
                'description' => $request->description,
                'active' => ($request->status ?? 'published') === 'published',
            ];

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
