<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Vai Aonde Influencer</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-black text-white fixed h-full overflow-y-auto shadow-xl">
            <div class="p-6 border-b border-gray-800">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="bg-[#FEB800] rounded-lg p-2">
                        <i class="fas fa-video text-black text-2xl"></i>
                    </div>
                    <div>
                        <img src="/logo.png" alt="Vai Aonde" class="h-10 w-auto" style="filter: brightness(0) invert(1);">
                        <p class="text-xs text-[#FEB800]">Area de Influenciador</p>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="bg-gray-900 rounded-lg p-4 mb-6 border border-[#FEB800]">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-[#FEB800] rounded-full flex items-center justify-center overflow-hidden">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover rounded-full">
                            @else
                                <i class="fas fa-user text-black text-xl"></i>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-sm truncate">{{ collect(explode(' ', trim(auth()->user()->name ?? '')))->first() }}</p>
                            <p class="text-xs text-gray-400">@ {{ auth()->user()->username }}</p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-700">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-400">Saldo:</span>
                            <span class="font-bold text-[#FEB800]">R$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="space-y-1">
                    <a href="{{ route('influencer.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.dashboard') ? 'bg-gray-900 border-l-4 border-[#FEB800] font-bold' : 'hover:bg-gray-900' }}">
                        <i class="fas fa-home w-5 {{ request()->routeIs('influencer.dashboard') ? 'text-[#FEB800]' : '' }}"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="{{ route('influencer.proposals') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.proposals*') ? 'bg-gray-900 border-l-4 border-[#FEB800] font-bold' : 'hover:bg-gray-900' }}">
                        <i class="fas fa-handshake w-5 {{ request()->routeIs('influencer.proposals*') ? 'text-[#FEB800]' : '' }}"></i>
                        <span>Propostas</span>
                    </a>
                    
                    <a href="{{ route('influencer.videos.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.videos*') ? 'bg-gray-900 border-l-4 border-[#FEB800] font-bold' : 'hover:bg-gray-900' }}">
                        <i class="fas fa-video w-5 {{ request()->routeIs('influencer.videos*') ? 'text-[#FEB800]' : '' }}"></i>
                        <span>Meus Vídeos</span>
                    </a>
                    
                    <a href="{{ route('influencer.chats') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.chats*') ? 'bg-gray-900 border-l-4 border-[#FEB800] font-bold' : 'hover:bg-gray-900' }}">
                        <i class="fas fa-comments w-5 {{ request()->routeIs('influencer.chats*') ? 'text-[#FEB800]' : '' }}"></i>
                        <span>Conversas</span>
                    </a>
                    
                    <a href="{{ route('influencer.wallet') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.wallet*') ? 'bg-gray-900 border-l-4 border-[#FEB800] font-bold' : 'hover:bg-gray-900' }}">
                        <i class="fas fa-wallet w-5 {{ request()->routeIs('influencer.wallet*') ? 'text-[#FEB800]' : '' }}"></i>
                        <span>Carteira</span>
                    </a>
                    
                    <div class="pt-4 mt-4 border-t border-gray-800">
                        <!-- Help Menu -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition hover:bg-gray-900">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-question-circle w-5 text-[#FEB800]"></i>
                                    <span>Ajuda</span>
                                </div>
                                <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            
                            <div x-show="open" x-transition class="mt-2 ml-4 space-y-1" style="display: none;">
                                <a href="https://wa.me/{{ \App\Models\Setting::get('help_whatsapp', '5511999999999') }}?text=Olá! Preciso de ajuda com a plataforma Vai Aonde" target="_blank" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition hover:bg-gray-900">
                                    <i class="fab fa-whatsapp w-5 text-green-500"></i>
                                    <span class="text-sm">WhatsApp</span>
                                </a>
                                <a href="mailto:{{ \App\Models\Setting::get('help_email', 'ajuda@vaiaonde.com.br') }}?subject=Suporte - Influenciador" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition hover:bg-gray-900">
                                    <i class="fas fa-envelope w-5 text-blue-500"></i>
                                    <span class="text-sm">Email</span>
                                </a>
                            </div>
                        </div>
                        
                        <a href="{{ route('influencer.profile') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.profile') ? 'bg-gray-900 border-l-4 border-[#FEB800] font-bold' : 'hover:bg-gray-900' }}">
                            <i class="fas fa-user-edit w-5 {{ request()->routeIs('influencer.profile') ? 'text-[#FEB800]' : '' }}"></i>
                            <span>Meu Perfil</span>
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition hover:bg-gray-900 text-left">
                                <i class="fas fa-sign-out-alt w-5 text-red-500"></i>
                                <span class="text-red-500">Sair</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <!-- Top Bar -->
            <header class="bg-white shadow-md border-b-4 border-[#FEB800] sticky top-0 z-10">
                <div class="px-8 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-black">@yield('page-title', 'Dashboard')</h2>
                            <p class="text-sm text-gray-600">@yield('page-subtitle', 'Bem-vindo ao painel de influenciadores')</p>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="relative p-2 text-black hover:bg-[#FEB800] rounded-lg transition">
                                    <i class="fas fa-bell text-xl"></i>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border-2 border-[#FEB800]" style="display: none;">
                                    <div class="p-4 border-b border-gray-200 bg-[#FEB800]">
                                        <h3 class="font-semibold text-black">Notificações</h3>
                                    </div>
                                    <div class="max-h-96 overflow-y-auto">
                                        <div class="p-4 text-center text-gray-500">
                                            <i class="fas fa-bell-slash text-3xl mb-2"></i>
                                            <p class="text-sm">Nenhuma notificação</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Menu -->
                            <div class="flex items-center space-x-3 px-4 py-2 bg-black text-white rounded-lg border-2 border-[#FEB800] shadow-lg">
                                <div class="w-10 h-10 bg-[#FEB800] rounded-full flex items-center justify-center text-black overflow-hidden">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-user"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-[#FEB800]">Influenciador</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-8">
                @if(session('success'))
                <div class="mb-6 bg-[#FEB800] bg-opacity-10 border-l-4 border-[#FEB800] text-black px-4 py-3 rounded flex items-center shadow">
                    <i class="fas fa-check-circle text-[#FEB800] mr-3 text-xl"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded flex items-center shadow">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 text-xl"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded shadow">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3 text-xl"></i>
                        <span class="font-semibold">Erros encontrados:</span>
                    </div>
                    <ul class="list-disc list-inside ml-6">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Refresh CSRF token periodically to prevent 419 errors
        setInterval(function() {
            fetch('/csrf-token')
                .then(response => response.json())
                .then(data => {
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                })
                .catch(error => console.log('Error refreshing CSRF token'));
        }, 600000); // Refresh every 10 minutes
    </script>

    @stack('scripts')
</body>
</html>
