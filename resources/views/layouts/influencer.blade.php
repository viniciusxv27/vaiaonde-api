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
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-purple-700 to-purple-900 text-white fixed h-full overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="bg-white rounded-lg p-2">
                        <i class="fas fa-video text-purple-700 text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Vai Aonde</h1>
                        <p class="text-xs text-purple-200">Influencer Panel</p>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="bg-purple-800 bg-opacity-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-sm truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-purple-200">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-purple-700">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-purple-200">Saldo:</span>
                            <span class="font-bold">R$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="space-y-1">
                    <a href="{{ route('influencer.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.dashboard') ? 'bg-purple-800' : 'hover:bg-purple-800 hover:bg-opacity-50' }}">
                        <i class="fas fa-home w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="{{ route('influencer.proposals') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.proposals*') ? 'bg-purple-800' : 'hover:bg-purple-800 hover:bg-opacity-50' }}">
                        <i class="fas fa-handshake w-5"></i>
                        <span>Propostas</span>
                    </a>
                    
                    <a href="{{ route('influencer.videos') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.videos*') ? 'bg-purple-800' : 'hover:bg-purple-800 hover:bg-opacity-50' }}">
                        <i class="fas fa-video w-5"></i>
                        <span>Meus Vídeos</span>
                    </a>
                    
                    <a href="{{ route('influencer.chats') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.chats*') ? 'bg-purple-800' : 'hover:bg-purple-800 hover:bg-opacity-50' }}">
                        <i class="fas fa-comments w-5"></i>
                        <span>Conversas</span>
                    </a>
                    
                    <a href="{{ route('influencer.wallet') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.wallet*') ? 'bg-purple-800' : 'hover:bg-purple-800 hover:bg-opacity-50' }}">
                        <i class="fas fa-wallet w-5"></i>
                        <span>Carteira</span>
                    </a>
                    
                    <div class="pt-4 mt-4 border-t border-purple-700">
                        <a href="{{ route('influencer.profile') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('influencer.profile') ? 'bg-purple-800' : 'hover:bg-purple-800 hover:bg-opacity-50' }}">
                            <i class="fas fa-user-edit w-5"></i>
                            <span>Meu Perfil</span>
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition hover:bg-purple-800 hover:bg-opacity-50 text-left">
                                <i class="fas fa-sign-out-alt w-5"></i>
                                <span>Sair</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm sticky top-0 z-10">
                <div class="px-8 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                            <p class="text-sm text-gray-500">@yield('page-subtitle', 'Bem-vindo ao painel de influenciadores')</p>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                                    <i class="fas fa-bell text-xl"></i>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border" style="display: none;">
                                    <div class="p-4 border-b">
                                        <h3 class="font-semibold">Notificações</h3>
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
                            <div class="flex items-center space-x-3 px-4 py-2 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">Influenciador</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-8">
                @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    {{ session('error') }}
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-circle mr-3"></i>
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

    @stack('scripts')
</body>
</html>
