<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\Video;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\FeaturedPlace;
use App\Models\PartnerSubscription;
use App\Models\PartnerSubscriptionPlan;
use App\Models\AbacatePayBilling;
use App\Models\Boost;
use App\Models\Chat;
use App\Models\Message;
use App\Models\PlaceImage;
use App\Services\AbacatePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PartnerWebController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user->isProprietario()) {
            return redirect()->route('login')->with('error', 'Acesso negado');
        }

        $placeIds = Place::where('owner_id', $user->id)->pluck('id');

        $stats = [
            'total_places' => $placeIds->count(),
            'total_mentions' => Video::whereIn('place_id', $placeIds)->count(),
            'active_contracts' => Proposal::whereIn('place_id', $placeIds)
                ->whereIn('status', ['pending', 'accepted'])
                ->count(),
            'total_views' => Video::whereIn('place_id', $placeIds)->sum('views_count'),
        ];

        $recentVideos = Video::whereIn('place_id', $placeIds)
            ->with(['user', 'place'])
            ->latest()
            ->take(5)
            ->get();

        $activeProposals = Proposal::whereIn('place_id', $placeIds)
            ->whereIn('status', ['pending', 'accepted'])
            ->with(['influencer', 'place'])
            ->latest()
            ->take(5)
            ->get();

        // Dados para gráficos
        $chartData = [
            'dates' => collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('d/m')),
            'views' => collect(range(6, 0))->map(function($i) use ($placeIds) {
                return Video::whereIn('place_id', $placeIds)
                    ->whereDate('created_at', now()->subDays($i))
                    ->sum('views_count');
            })
        ];

        $places = Place::where('owner_id', $user->id)->take(5)->get();
        $placeNames = $places->pluck('name');
        $placeViews = $places->map(function($place) {
            return Video::where('place_id', $place->id)->sum('views_count');
        });

        return view('partner.dashboard', compact(
            'stats',
            'recentVideos',
            'activeProposals',
            'chartData',
            'placeNames',
            'placeViews'
        ));
    }

    public function places()
    {
        $user = Auth::user();
        $places = Place::where('owner_id', $user->id)
            ->with('city')
            ->paginate(12);

        return view('partner.places', compact('places'));
    }

    public function createPlace()
    {
        $cities = \App\Models\City::orderBy('name')->get();
        $tipes = \App\Models\Tipe::orderBy('name')->get();
        $categories = \App\Models\Categorie::orderBy('name')->get();

        return view('partner.places-create', compact('cities', 'tipes', 'categories'));
    }

    public function storePlace(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'tipe_id' => 'required|exists:tipe,id',
            'city_id' => 'required|exists:city,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categorie,id',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'instagram_url' => 'nullable|string',
            'location_url' => 'nullable|string',
            'uber_url' => 'nullable|string',
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'card_image_index' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Buscar o tipo para definir o type
            $tipe = \App\Models\Tipe::find($request->tipe_id);
            
            if (!$tipe) {
                throw new \Exception('Tipo não encontrado com ID: ' . $request->tipe_id);
            }
            
            // Índice da imagem principal (padrão é a primeira - índice 0)
            $cardImageIndex = $request->card_image_index ?? 0;
            
            $cardImagePath = '/uploads/places/placeholder.jpg'; // Valor padrão
            
            // Upload da imagem principal como card_image
            if ($request->hasFile('images') && isset($request->file('images')[$cardImageIndex])) {
                $cardImage = $request->file('images')[$cardImageIndex];
                $cardImageName = time() . '_card_' . uniqid() . '.' . $cardImage->getClientOriginalExtension();
                $cardImage->storeAs('uploads/places', $cardImageName, 'public_uploads');
                $cardImagePath = '/uploads/places/' . $cardImageName;
            } elseif ($request->hasFile('images')) {
                // Fallback: usar primeira imagem se o índice não existir
                $cardImage = $request->file('images')[0];
                $cardImageName = time() . '_card_' . uniqid() . '.' . $cardImage->getClientOriginalExtension();
                $cardImage->storeAs('uploads/places', $cardImageName, 'public_uploads');
                $cardImagePath = '/uploads/places/' . $cardImageName;
            }
            
            // Upload da logo
            $logoPath = '';
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoName = time() . '_logo_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                $logo->storeAs('uploads/places/logos', $logoName, 'public_uploads');
                $logoPath = '/uploads/places/logos/' . $logoName;
            }
            
            \Log::info('Proprietário criando lugar:', [
                'user_id' => $user->id,
                'name' => $request->name,
                'type' => $tipe->name,
                'card_image' => $cardImagePath,
                'logo' => $logoPath,
            ]);
            
            $data = [
                'name' => $request->name,
                'type' => $tipe->name ?? 'lugar', // usar o nome do tipo
                'tipe_id' => $request->tipe_id,
                'city_id' => $request->city_id,
                'owner_id' => $user->id,
                'phone' => $request->phone ?? '',
                'review' => $request->description ?? '', // description vira review
                'is_active' => true,
                'categories_ids' => $request->categories ? implode(',', $request->categories) : '',
                'card_image' => $cardImagePath,
                'logo' => $logoPath,
                'instagram_url' => $request->instagram_url ?? '',
                'location_url' => $request->location_url ?? '',
                'uber_url' => $request->uber_url ?? '',
                'location' => $request->location ?? '',
                'hidden' => false,
                'top' => false,
            ];

            // Create place first
            $place = Place::create($data);
            
            \Log::info('Lugar criado pelo proprietário com ID: ' . $place->id);

            // Create coords after place if latitude and longitude provided
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $coords = \App\Models\Coords::create([
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'place_id' => $place->id,
                ]);
                $place->coords_id = $coords->id;
                $place->save();
                
                \Log::info('Coordenadas salvas para o lugar ID: ' . $place->id);
            }

            // Handle multiple images upload
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    try {
                        // Sanitizar nome do arquivo
                        $extension = $image->getClientOriginalExtension();
                        $filename = time() . '_' . $index . '_' . uniqid() . '.' . $extension;
                        
                        // Usar Storage do Laravel para mover o arquivo
                        $path = $image->storeAs('uploads/places', $filename, 'public_uploads');
                        
                        if (!$path) {
                            throw new \Exception("Falha ao salvar arquivo");
                        }
                        
                        \App\Models\PlaceImage::create([
                            'place_id' => $place->id,
                            'image_path' => '/uploads/places/' . $filename,
                            'is_primary' => $index === $cardImageIndex,
                            'order' => $index,
                        ]);
                        
                        \Log::info("Imagem {$index} salva para lugar {$place->id}: /uploads/places/{$filename}");
                        
                    } catch (\Exception $e) {
                        \Log::error("Erro ao processar imagem {$index}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        throw new \Exception("Erro ao fazer upload da imagem " . ($index + 1) . ": " . $e->getMessage());
                    }
                }
            }

            // If user has active subscription, link it to the place
            if ($user->partnerSubscription && $user->partnerSubscription->isActive()) {
                $place->subscription_id = $user->partnerSubscription->id;
                $place->save();
                
                \Log::info('Assinatura vinculada ao lugar ID: ' . $place->id);
            }

            DB::commit();
            
            \Log::info('Estabelecimento criado com sucesso pelo proprietário! ID: ' . $place->id);
            
            return redirect()->route('partner.places')->with('success', 'Estabelecimento cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erro ao cadastrar estabelecimento (Proprietário): ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->with('error', 'Erro ao cadastrar estabelecimento: ' . $e->getMessage())->withInput();
        }
    }

    public function editPlace($id)
    {
        $user = Auth::user();
        $place = Place::where('id', $id)->where('owner_id', $user->id)->firstOrFail();
        $cities = \App\Models\City::orderBy('name')->get();
        $tipes = \App\Models\Tipe::orderBy('name')->get();
        $categories = \App\Models\Categorie::orderBy('name')->get();

        return view('partner.places-edit', compact('place', 'cities', 'tipes', 'categories'));
    }

    public function updatePlace(Request $request, $id)
    {
        $user = Auth::user();
        $place = Place::where('id', $id)->where('owner_id', $user->id)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'tipe_id' => 'required|exists:tipe,id',
            'city_id' => 'required|exists:city,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categorie,id',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:place_images,id',
        ]);

        DB::beginTransaction();
        try {
            // Get tipe name
            $tipe = \App\Models\Tipe::findOrFail($request->tipe_id);
            
            $data = [
                'name' => $request->name,
                'type' => $tipe->name,
                'tipe_id' => $request->tipe_id,
                'categories_ids' => $request->filled('categories') ? implode(',', $request->categories) : null,
                'city_id' => $request->city_id,
                'description' => $request->description,
                'address' => $request->address,
            ];

            // Handle new images upload
            if ($request->hasFile('images')) {
                $currentMaxOrder = $place->images()->max('order') ?? -1;
                
                foreach ($request->file('images') as $index => $image) {
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('places', $filename, 'public');
                    
                    PlaceImage::create([
                        'place_id' => $place->id,
                        'image_path' => $path,
                        'is_primary' => $place->images()->count() === 0 && $index === 0,
                        'order' => $currentMaxOrder + $index + 1,
                    ]);
                }
            }

            // Delete selected images
            if ($request->filled('delete_images')) {
                PlaceImage::whereIn('id', $request->delete_images)
                    ->where('place_id', $place->id)
                    ->delete();
                    
                // Se deletou a imagem principal, definir outra como principal
                if (!$place->images()->where('is_primary', true)->exists()) {
                    $firstImage = $place->images()->first();
                    if ($firstImage) {
                        $firstImage->update(['is_primary' => true]);
                    }
                }
            }

            // Update coords if latitude and longitude provided
            if ($request->filled('latitude') && $request->filled('longitude')) {
                if ($place->coords_id) {
                    $place->coords->update([
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                    ]);
                } else {
                    $coords = \App\Models\Coords::create([
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'place_id' => $place->id,
                    ]);
                    $place->coords_id = $coords->id;
                }
            }

            $place->update($data);

            DB::commit();
            return redirect()->route('partner.places')->with('success', 'Estabelecimento atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar estabelecimento: ' . $e->getMessage())->withInput();
        }
    }

    public function deletePlace($id)
    {
        $user = Auth::user();
        $place = Place::where('id', $id)->where('owner_id', $user->id)->firstOrFail();
        
        $place->delete();
        
        return back()->with('success', 'Estabelecimento excluído com sucesso!');
    }

    public function videos()
    {
        $user = Auth::user();
        $placeIds = Place::where('owner_id', $user->id)->pluck('id');
        
        $videos = Video::whereIn('place_id', $placeIds)
            ->with(['user', 'place'])
            ->latest()
            ->paginate(12);

        return view('partner.videos', compact('videos'));
    }

    public function proposals(Request $request)
    {
        $user = Auth::user();
        $placeIds = Place::where('owner_id', $user->id)->pluck('id');
        
        $status = $request->get('status', 'all');
        
        $query = Proposal::whereIn('place_id', $placeIds)
            ->with(['influencer', 'place']);

        if ($status != 'all') {
            $query->where('status', $status);
        }

        $proposals = $query->latest()->paginate(10);

        $counts = [
            'total' => Proposal::whereIn('place_id', $placeIds)->count(),
            'pending' => Proposal::whereIn('place_id', $placeIds)->where('status', 'pending')->count(),
            'accepted' => Proposal::whereIn('place_id', $placeIds)->where('status', 'accepted')->count(),
            'completed' => Proposal::whereIn('place_id', $placeIds)->where('status', 'completed')->count(),
            'rejected' => Proposal::whereIn('place_id', $placeIds)->where('status', 'rejected')->count(),
        ];

        return view('partner.proposals', compact('proposals', 'counts'));
    }

    public function acceptProposal($id)
    {
        $user = Auth::user();
        $proposal = Proposal::findOrFail($id);
        
        if ($proposal->place->owner_id != $user->id) {
            return back()->with('error', 'Sem permissão');
        }

        if ($user->wallet_balance < $proposal->amount) {
            return back()->with('error', 'Saldo insuficiente na carteira');
        }

        $proposal->update([
            'status' => 'accepted',
            'accepted_at' => now()
        ]);

        return back()->with('success', 'Proposta aceita com sucesso!');
    }

    public function rejectProposal($id)
    {
        $user = Auth::user();
        $proposal = Proposal::findOrFail($id);
        
        if ($proposal->place->owner_id != $user->id) {
            return back()->with('error', 'Sem permissão');
        }

        $proposal->update(['status' => 'rejected']);

        return back()->with('success', 'Proposta rejeitada');
    }

    public function wallet()
    {
        $user = Auth::user();
        
        $transactions = Transaction::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $stats = [
            'total_deposits' => Transaction::where('user_id', $user->id)
                ->where('type', 'deposit')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_spent' => Transaction::where('user_id', $user->id)
                ->whereIn('type', ['transfer_out', 'featured_payment'])
                ->where('status', 'completed')
                ->sum('amount'),
            'total_transactions' => Transaction::where('user_id', $user->id)->count(),
        ];

        return view('partner.wallet', compact('transactions', 'stats'));
    }

    public function depositWallet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'payment_method' => 'required|in:pix,card',
            'stripeToken' => 'required_if:payment_method,card'
        ]);

        $user = Auth::user();
        $paymentMethod = $request->payment_method;

        // ========== PAGAMENTO VIA PIX (AbacatePay) ==========
        if ($paymentMethod === 'pix') {
            try {
                $abacatePayService = new \App\Services\AbacatePayService();
                
                $result = $abacatePayService->createPixQrCode(
                    $request->amount,
                    'Recarga de saldo - VaiAonde - User: ' . $user->id,
                    [
                        'name' => $user->name,
                        'cellphone' => $user->phone ?? '',
                        'email' => $user->email,
                        'taxId' => $user->cpf ?? ''
                    ],
                    [
                        'user_id' => $user->id,
                        'type' => 'wallet_deposit'
                    ]
                );

                if (!$result['success']) {
                    return back()->with('error', $result['error'] ?? 'Erro ao gerar PIX');
                }

                // Verificar se temos os dados necessários
                if (empty($result['id'])) {
                    Log::error('AbacatePay retornou sucesso mas sem ID', ['result' => $result]);
                    return back()->with('error', 'Erro: QR Code gerado mas sem identificador. Entre em contato com o suporte.');
                }

                if (empty($result['qr_code'])) {
                    Log::error('AbacatePay retornou sucesso mas sem QR Code', ['result' => $result]);
                    return back()->with('error', 'Erro: Não foi possível gerar o QR Code. Tente novamente.');
                }

                // NÃO salvar no banco - apenas retornar para exibição
                // O sistema vai verificar o status via polling JavaScript
                return back()->with([
                    'success' => 'QR Code PIX gerado com sucesso! Aguardando pagamento...',
                    'pix_qr_code' => $result['qr_code'],
                    'pix_qr_code_url' => $result['qr_code_url'],
                    'pix_id' => $result['id'],
                    'pix_amount' => $request->amount,
                    'pix_expires_at' => $result['expires_at'],
                    'show_pix_modal' => true
                ]);

            } catch (\Exception $e) {
                Log::error('Erro ao gerar PIX', ['error' => $e->getMessage()]);
                return back()->with('error', 'Erro ao gerar PIX: ' . $e->getMessage());
            }
        }

        // ========== PAGAMENTO VIA CARTÃO (Stripe) ==========
        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            // Criar cobrança no Stripe
            $charge = \Stripe\Charge::create([
                'amount' => $request->amount * 100, // Stripe usa centavos
                'currency' => 'brl',
                'source' => $request->stripeToken,
                'description' => 'Recarga de saldo - VaiAonde - User: ' . $user->id,
                'metadata' => [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]
            ]);

            // Se o pagamento foi bem-sucedido, adicionar saldo
            $user->increment('wallet_balance', $request->amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'balance_before' => $user->wallet_balance - $request->amount,
                'balance_after' => $user->wallet_balance,
                'description' => 'Depósito via cartão - Stripe',
                'payment_method' => 'card',
                'status' => 'completed',
                'metadata' => json_encode([
                    'stripe_charge_id' => $charge->id,
                    'stripe_payment_intent' => $charge->payment_intent ?? null,
                ])
            ]);

            return back()->with('success', 'Depósito de R$ ' . number_format($request->amount, 2, ',', '.') . ' realizado com sucesso!');

        } catch (\Stripe\Exception\CardException $e) {
            return back()->with('error', 'Erro no cartão: ' . $e->getError()->message);
        } catch (\Stripe\Exception\RateLimitException $e) {
            return back()->with('error', 'Muitas requisições. Tente novamente em alguns instantes.');
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return back()->with('error', 'Requisição inválida: ' . $e->getMessage());
        } catch (\Stripe\Exception\AuthenticationException $e) {
            return back()->with('error', 'Erro de autenticação com Stripe.');
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            return back()->with('error', 'Erro de conexão com Stripe.');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return back()->with('error', 'Erro na API do Stripe: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }
    }

    public function withdrawWallet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:20',
            'pix_key' => 'required|string'
        ]);

        $user = Auth::user();

        if ($user->wallet_balance < $request->amount) {
            return back()->with('error', 'Saldo insuficiente');
        }

        $user->decrement('wallet_balance', $request->amount);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'balance_before' => $user->wallet_balance + $request->amount,
            'balance_after' => $user->wallet_balance,
            'description' => 'Saque via PIX',
            'payment_method' => 'pix',
            'pix_key' => $request->pix_key,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Saque solicitado! Será processado em até 24h.');
    }

    /**
     * Verifica o status de um pagamento PIX
     */
    public function checkPixPayment(Request $request)
    {
        $request->validate([
            'pix_id' => 'required|string'
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

            // Se foi pago, creditar saldo
            if ($result['paid']) {
                $user = Auth::user();
                $amount = $request->amount;

                // Verificar se já não creditou (evitar duplicação)
                $existingTransaction = Transaction::where('user_id', $user->id)
                    ->where('description', 'LIKE', '%PIX%' . $request->pix_id . '%')
                    ->where('type', 'deposit')
                    ->first();

                if (!$existingTransaction) {
                    // Creditar saldo
                    $user->increment('wallet_balance', $amount);

                    // Registrar transação
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
                    'status' => $result['status'],
                    'new_balance' => $user->wallet_balance
                ]);
            }

            return response()->json([
                'success' => true,
                'paid' => false,
                'status' => $result['status']
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar PIX', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Erro ao verificar pagamento'
            ], 500);
        }
    }

    public function featured()
    {
        $user = Auth::user();
        $places = Place::where('owner_id', $user->id)->get();
        
        $activeFeatured = FeaturedPlace::whereIn('place_id', $places->pluck('id'))
            ->where('ends_at', '>', now())
            ->with('place.city')
            ->get();

        return view('partner.featured', compact('places', 'activeFeatured'));
    }

    public function purchaseFeatured(Request $request)
    {
        $request->validate([
            'place_id' => 'required|exists:place,id'
        ]);

        $user = Auth::user();
        $place = Place::findOrFail($request->place_id);

        if ($place->owner_id != $user->id) {
            return back()->with('error', 'Você não possui este lugar');
        }

        $amount = 39.90;

        if ($user->wallet_balance < $amount) {
            return back()->with('error', 'Saldo insuficiente. Adicione saldo na sua carteira.');
        }

        // Verifica se já está em destaque
        $existingFeatured = FeaturedPlace::where('place_id', $place->id)
            ->where('ends_at', '>', now())
            ->first();

        if ($existingFeatured) {
            return back()->with('error', 'Este lugar já está em destaque');
        }

        DB::beginTransaction();
        try {
            // Debita da carteira
            $user->decrement('wallet_balance', $amount);

            // Cria destaque
            FeaturedPlace::create([
                'place_id' => $place->id,
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
                'is_active' => true
            ]);

            // Registra transação
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'featured_payment',
                'amount' => $amount,
                'balance_before' => $user->wallet_balance + $amount,
                'balance_after' => $user->wallet_balance,
                'description' => "Destaque: {$place->name} - 30 dias",
                'status' => 'completed'
            ]);

            DB::commit();

            return back()->with('success', 'Lugar destacado com sucesso! Estará visível por 30 dias.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao processar pagamento');
        }
    }

    public function plans()
    {
        return view('partner.plans');
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:partner_subscription_plans,id'
        ]);

        $user = Auth::user();
        $plan = PartnerSubscriptionPlan::findOrFail($request->plan_id);

        // Verifica saldo
        if ($plan->price > 0 && $user->wallet_balance < $plan->price) {
            return back()->with('error', 'Saldo insuficiente. Adicione saldo na carteira primeiro.');
        }

        DB::beginTransaction();
        try {
            // Cancela assinatura anterior se existir
            if ($user->partnerSubscription) {
                $user->partnerSubscription->cancel();
            }

            // Cria nova assinatura
            $subscription = PartnerSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'last_payment_date' => now(),
                'next_payment_date' => now()->addMonth(),
                'status' => 'active',
                'auto_renew' => true
            ]);

            // Debita da carteira se não for gratuito
            if ($plan->price > 0) {
                $user->decrement('wallet_balance', floatval($plan->price));

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'transfer_out',
                    'amount' => $plan->price,
                    'balance_before' => $user->wallet_balance + floatval($plan->price),
                    'balance_after' => $user->wallet_balance,
                    'description' => "Assinatura: {$plan->name}",
                    'status' => 'completed'
                ]);
            }

            // Atualiza lugares do proprietário com a nova assinatura
            Place::where('owner_id', $user->id)->update([
                'subscription_id' => $subscription->id,
                'is_active' => true,
                'deactivation_reason' => null
            ]);

            DB::commit();

            return back()->with('success', "Assinatura do {$plan->name} ativada com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao processar assinatura: ' . $e->getMessage());
        }
    }

    public function cancelSubscription()
    {
        $user = Auth::user();

        if (!$user->partnerSubscription) {
            return back()->with('error', 'Você não possui uma assinatura ativa');
        }

        $user->partnerSubscription->cancel();

        return back()->with('success', 'Assinatura cancelada. Você ainda terá acesso até o fim do período pago.');
    }

    public function profile()
    {
        return view('partner.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14',
            'pix_key' => 'nullable|string|max:255',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:6|confirmed',
        ]);

        // Limpar CPF (remover pontos e hífen)
        $cpf = null;
        if ($request->filled('cpf')) {
            $cpf = preg_replace('/[^0-9]/', '', $request->cpf);
            
            // Validar se tem 11 dígitos
            if (strlen($cpf) !== 11) {
                return back()->with('error', 'CPF inválido. Deve conter 11 dígitos.');
            }
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'cpf' => $cpf,
            'pix_key' => $request->pix_key,
        ];

        if ($request->filled('password')) {
            if ($request->filled('current_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->with('error', 'Senha atual incorreta');
                }
                $data['password'] = Hash::make($request->password);
            }
        }

        $user->update($data);

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function settings()
    {
        return view('partner.settings');
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'notification_email' => 'nullable|boolean',
            'notification_sms' => 'nullable|boolean',
            'auto_renew_subscription' => 'nullable|boolean',
        ]);

        // Update subscription auto-renew if user has one
        if ($user->partnerSubscription) {
            $user->partnerSubscription->update([
                'auto_renew' => $request->boolean('auto_renew_subscription'),
            ]);
        }

        return back()->with('success', 'Configurações atualizadas com sucesso!');
    }

    public function chats()
    {
        $user = Auth::user();
        $placeIds = Place::where('owner_id', $user->id)->pluck('id');
        
        $chats = Chat::where('place_id', $placeIds)
            ->with(['influencer', 'place'])
            ->withCount(['messages as unread_count' => function($query) {
                $query->where('is_read', false)->where('user_id', '!=', auth()->id());
            }])
            ->latest('updated_at')
            ->get();

        return view('partner.chats', compact('chats'));
    }

    public function showChat($id)
    {
        $user = Auth::user();
        $chat = Chat::whereHas('place', function($query) use ($user) {
                $query->where('owner_id', $user->id);
            })
            ->with(['messages.user', 'influencer', 'place'])
            ->findOrFail($id);

        // Marcar mensagens como lidas
        Message::where('chat_id', $chat->id)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('partner.chat', compact('chat'));
    }

    public function sendMessage(Request $request, $id)
    {
        $user = Auth::user();
        $chat = Chat::whereHas('place', function($query) use ($user) {
                $query->where('owner_id', $user->id);
            })
            ->findOrFail($id);

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

    public function boostVideo(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:videos,id',
            'amount' => 'required|numeric|min:10',
            'days' => 'required|integer|min:1|max:30',
        ]);

        $user = Auth::user();
        $video = Video::findOrFail($request->video_id);

        // Verificar se o vídeo pertence a um lugar do proprietário
        $placeIds = Place::where('owner_id', $user->id)->pluck('id');
        if (!$placeIds->contains($video->place_id)) {
            return back()->with('error', 'Você não tem permissão para impulsionar este vídeo');
        }

        // Verificar saldo
        if ($user->wallet_balance < $request->amount) {
            return back()->with('error', 'Saldo insuficiente');
        }

        DB::beginTransaction();
        try {
            $dailyBudget = $request->amount / $request->days;

            // Criar impulsionamento
            $boost = Boost::create([
                'video_id' => $request->video_id,
                'user_id' => $user->id,
                'amount' => $request->amount,
                'days' => $request->days,
                'daily_budget' => $dailyBudget,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays($request->days),
            ]);

            // Debitar saldo
            $user->decrement('wallet_balance', $request->amount);

            // Registrar transação
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'boost',
                'amount' => -$request->amount,
                'balance_before' => $user->wallet_balance + $request->amount,
                'balance_after' => $user->wallet_balance,
                'description' => "Impulsionamento de vídeo por {$request->days} dias - Budget diário: R$ " . number_format($dailyBudget, 2, ',', '.'),
                'status' => 'completed'
            ]);

            DB::commit();
            return back()->with('success', 'Vídeo impulsionado com sucesso! Budget diário: R$ ' . number_format($dailyBudget, 2, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao impulsionar vídeo: ' . $e->getMessage());
        }
    }
}
