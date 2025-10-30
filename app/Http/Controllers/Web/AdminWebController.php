<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Place;
use App\Models\Video;
use App\Models\Banner;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\FeaturedPlace;
use App\Models\PartnerSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AdminWebController extends Controller
{
    public function dashboard()
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('login')->with('error', 'Acesso negado');
        }
        $stats = [
            'total_users' => User::count(),
            'total_videos' => Video::count(),
            'active_proposals' => Proposal::whereIn('status', ['pending', 'accepted'])->count(),
            'total_revenue' => Transaction::where('type', 'deposit')->sum('amount'),
        ];

        $recentVideos = Video::with(['user', 'place'])
            ->latest()
            ->take(10)
            ->get();

        $recentTransactions = Transaction::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Dados para gráficos
        $userGrowth = collect(range(11, 0))->map(function($i) {
            return User::whereDate('created_at', now()->subMonths($i)->startOfMonth())
                ->count();
        });

        $monthlyRevenue = collect(range(11, 0))->map(function($i) {
            return Transaction::where('type', 'deposit')
                ->whereYear('created_at', now()->subMonths($i)->year)
                ->whereMonth('created_at', now()->subMonths($i)->month)
                ->sum('amount');
        });

        return view('admin.dashboard', compact(
            'stats',
            'recentVideos',
            'recentTransactions',
            'userGrowth',
            'monthlyRevenue'
        ));
    }

    // Users CRUD
    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:comum,assinante,proprietario,influenciador',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_admin' => $request->has('is_admin'),
        ]);

        return redirect()->route('admin.users')->with('success', 'Usuário criado com sucesso!');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:comum,assinante,proprietario,influenciador',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_admin' => $request->has('is_admin'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id == Auth::id()) {
            return back()->with('error', 'Você não pode deletar sua própria conta');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Usuário deletado com sucesso!');
    }

    // Places CRUD
    public function places()
    {
        $places = Place::with(['city', 'owner'])->latest()->paginate(20);
        return view('admin.places.index', compact('places'));
    }

    public function createPlace()
    {
        $tipes = \App\Models\Tipe::orderBy('name')->get();
        $categories = \App\Models\Categorie::with('tipe')->orderBy('name')->get();
        return view('admin.places.create', compact('tipes', 'categories'));
    }

    public function storePlace(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:lugar,restaurante,evento',
            'city_id' => 'required|exists:city,id',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'card_image_index' => 'nullable|integer|min:0',
            'owner_id' => 'nullable|exists:users,id',
            'plan_id' => 'nullable|exists:partner_subscription_plans,id',
            'phone' => 'nullable|string',
            'review' => 'required|string',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'instagram_url' => 'nullable|string',
            'location_url' => 'nullable|string',
            'uber_url' => 'nullable|string',
            'location' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categorie,id',
        ]);

        DB::beginTransaction();
        try {
            // Get tipe_id from type name
            $tipe = \App\Models\Tipe::where('name', $request->type)->first();
            
            if (!$tipe) {
                throw new \Exception('Tipo não encontrado: ' . $request->type);
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
            
            \Log::info('Dados do lugar a serem salvos:', [
                'name' => $request->name,
                'type' => $request->type,
                'tipe_id' => $tipe->id,
                'card_image' => $cardImagePath,
                'logo' => $logoPath,
            ]);
            
            $data = [
                'name' => $request->name,
                'type' => $request->type,
                'tipe_id' => $tipe ? $tipe->id : null,
                'city_id' => $request->city_id,
                'owner_id' => $request->owner_id,
                'phone' => $request->phone ?? '',
                'review' => $request->review,
                'categories_ids' => $request->filled('categories') ? implode(',', $request->categories) : '',
                'card_image' => $cardImagePath,
                'logo' => $logoPath,
                'instagram_url' => $request->instagram_url ?? '',
                'location_url' => $request->location_url ?? '',
                'uber_url' => $request->uber_url ?? '',
                'location' => $request->location ?? '',
                'hidden' => false,
                'top' => false,
            ];

            // Se tem proprietário e plano, criar assinatura
            if ($request->owner_id && $request->plan_id) {
                $subscription = PartnerSubscription::create([
                    'user_id' => $request->owner_id,
                    'plan_id' => $request->plan_id,
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                    'last_payment_date' => now(),
                    'next_payment_date' => now()->addMonth(),
                    'status' => 'active',
                    'auto_renew' => true
                ]);

                $data['subscription_id'] = $subscription->id;
                $data['is_active'] = true;
            } else {
                // Sem assinatura, mas ativo (cadastro gratuito)
                $data['is_active'] = true;
            }

            // Create place first
            $place = Place::create($data);
            
            \Log::info('Lugar criado com ID: ' . $place->id);

            // Upload de todas as imagens para place_images
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
                        
                        \Log::info("Imagem {$index} salva: /uploads/places/{$filename}");
                        
                    } catch (\Exception $e) {
                        \Log::error("Erro ao processar imagem {$index}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        throw new \Exception("Erro ao fazer upload da imagem " . ($index + 1) . ": " . $e->getMessage());
                    }
                }
            }

            // Create coords after place if latitude and longitude provided
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $coords = \App\Models\Coords::create([
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'place_id' => $place->id,
                ]);
                $place->coords_id = $coords->id;
                $place->save();
            }

            DB::commit();
            
            \Log::info('Lugar criado com sucesso! ID: ' . $place->id);

            return redirect()->route('admin.places')->with('success', 'Lugar criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erro ao criar lugar: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->with('error', 'Erro ao criar lugar: ' . $e->getMessage())->withInput();
        }
    }

    public function deletePlace($id)
    {
        $place = Place::findOrFail($id);
        $place->delete();

        return redirect()->route('admin.places')->with('success', 'Lugar deletado com sucesso!');
    }

    // Videos Management
    public function videos()
    {
        $videos = Video::with(['user', 'place'])->latest()->paginate(20);
        return view('admin.videos.index', compact('videos'));
    }

    public function deleteVideo($id)
    {
        $video = Video::findOrFail($id);
        $video->delete();

        return redirect()->route('admin.videos')->with('success', 'Vídeo deletado com sucesso!');
    }

    // Proposals Management
    public function proposals()
    {
        $proposals = Proposal::with(['influencer', 'place'])->latest()->paginate(20);
        return view('admin.proposals.index', compact('proposals'));
    }

    // Transactions Management
    public function transactions()
    {
        $transactions = Transaction::with('user')->latest()->paginate(20);
        return view('admin.transactions.index', compact('transactions'));
    }

    public function approveWithdrawal($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->type != 'withdrawal' || $transaction->status != 'pending') {
            return back()->with('error', 'Transação inválida');
        }

        $transaction->update(['status' => 'completed']);

        return back()->with('success', 'Saque aprovado com sucesso!');
    }

    public function rejectWithdrawal($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->type != 'withdrawal' || $transaction->status != 'pending') {
            return back()->with('error', 'Transação inválida');
        }

        DB::beginTransaction();
        try {
            // Estorna o valor para o usuário
            $transaction->user->increment('wallet_balance', floatval($transaction->amount));
            
            $transaction->update(['status' => 'failed']);

            DB::commit();

            return back()->with('success', 'Saque rejeitado e valor estornado');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao processar rejeição');
        }
    }

    // Banners CRUD
    public function banners()
    {
        $banners = Banner::latest()->paginate(20);
        return view('admin.banners.index', compact('banners'));
    }

    public function createBanner()
    {
        return view('admin.banners.create');
    }

    public function storeBanner(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $data = [
            'title' => $request->title,
            'link' => $request->link,
            'is_active' => $request->boolean('is_active', true),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('banners', $filename, 'public');
            $data['image_url'] = asset('storage/' . $path);
        }

        Banner::create($data);

        return redirect()->route('admin.banners')->with('success', 'Banner criado com sucesso!');
    }

    public function editBanner($id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin.banners.edit', compact('banner'));
    }

    public function updateBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $data = [
            'title' => $request->title,
            'link' => $request->link,
            'is_active' => $request->boolean('is_active'),
        ];

        // Handle new image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('banners', $filename, 'public');
            $data['image_url'] = asset('storage/' . $path);
        }

        $banner->update($data);

        return redirect()->route('admin.banners')->with('success', 'Banner atualizado com sucesso!');
    }

    public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return redirect()->route('admin.banners')->with('success', 'Banner deletado com sucesso!');
    }

    // Profile & Settings
    public function profile()
    {
        return view('admin.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
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
        $settings = \App\Models\Setting::getAllSettings();
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'featured_price' => 'required|numeric|min:0',
            'min_withdrawal' => 'required|numeric|min:0',
        ]);

        \App\Models\Setting::set('app_name', $request->app_name);
        \App\Models\Setting::set('contact_email', $request->contact_email);
        \App\Models\Setting::set('featured_price', $request->featured_price);
        \App\Models\Setting::set('min_withdrawal', $request->min_withdrawal);

        return back()->with('success', 'Configurações atualizadas com sucesso!');
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return back()->with('success', 'Cache limpo com sucesso!');
    }

    public function subscriptions()
    {
        $subscriptions = PartnerSubscription::with(['user', 'plan'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.subscriptions', compact('subscriptions'));
    }

    // Cities Management
    public function cities()
    {
        $cities = \App\Models\City::withCount('places')->orderBy('name')->paginate(20);
        return view('admin.cities.index', compact('cities'));
    }

    public function createCity()
    {
        return view('admin.cities.create');
    }

    public function storeCity(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:city,name',
        ]);

        \App\Models\City::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.cities')->with('success', 'Cidade cadastrada com sucesso!');
    }

    public function editCity($id)
    {
        $city = \App\Models\City::findOrFail($id);
        return view('admin.cities.edit', compact('city'));
    }

    public function updateCity(Request $request, $id)
    {
        $city = \App\Models\City::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:city,name,' . $id,
        ]);

        $city->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.cities')->with('success', 'Cidade atualizada com sucesso!');
    }

    public function deleteCity($id)
    {
        $city = \App\Models\City::findOrFail($id);
        $city->delete();

        return back()->with('success', 'Cidade excluída com sucesso!');
    }

    // Categories Management
    public function categories()
    {
        $categories = \App\Models\Categorie::with('tipe')->orderBy('name')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        $tipes = \App\Models\Tipe::orderBy('name')->get();
        return view('admin.categories.create', compact('tipes'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categorie,name',
            'tipe_id' => 'required|exists:tipe,id',
        ]);

        \App\Models\Categorie::create([
            'name' => $request->name,
            'tipe_id' => $request->tipe_id,
        ]);

        return redirect()->route('admin.categories')->with('success', 'Categoria cadastrada com sucesso!');
    }

    public function editCategory($id)
    {
        $category = \App\Models\Categorie::findOrFail($id);
        $tipes = \App\Models\Tipe::orderBy('name')->get();
        return view('admin.categories.edit', compact('category', 'tipes'));
    }

    public function updateCategory(Request $request, $id)
    {
        $category = \App\Models\Categorie::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categorie,name,' . $id,
            'tipe_id' => 'required|exists:tipe,id',
        ]);

        $category->update([
            'name' => $request->name,
            'tipe_id' => $request->tipe_id,
        ]);

        return redirect()->route('admin.categories')->with('success', 'Categoria atualizada com sucesso!');
    }

    public function deleteCategory($id)
    {
        $category = \App\Models\Categorie::findOrFail($id);
        $category->delete();

        return back()->with('success', 'Categoria excluída com sucesso!');
    }
}
