<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <title>@yield('title') - Área do Parceiro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="bg-gradient-to-b from-blue-600 to-blue-800 text-white transition-all duration-300">
            <div class="p-4 flex items-center justify-between">
                <div x-show="sidebarOpen" class="flex items-center">
                    <img src="{{ asset('logo.png') }}" alt="VaiAonde" class="h-8">
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-white">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="mt-8">
                <a href="{{ route('partner.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-blue-700 {{ request()->routeIs('partner.dashboard') ? 'bg-blue-700 border-l-4 border-white' : '' }}">
                    <i class="fas fa-chart-line w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Dashboard</span>
                </a>
                
                <a href="{{ route('partner.places') }}" class="flex items-center px-6 py-3 hover:bg-blue-700 {{ request()->routeIs('partner.places') ? 'bg-blue-700 border-l-4 border-white' : '' }}">
                    <i class="fas fa-store w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Meus Lugares</span>
                </a>
                
                <a href="{{ route('partner.videos') }}" class="flex items-center px-6 py-3 hover:bg-blue-700 {{ request()->routeIs('partner.videos') ? 'bg-blue-700 border-l-4 border-white' : '' }}">
                    <i class="fas fa-video w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Vídeos</span>
                </a>
                
                <a href="{{ route('partner.proposals') }}" class="flex items-center px-6 py-3 hover:bg-blue-700 {{ request()->routeIs('partner.proposals') ? 'bg-blue-700 border-l-4 border-white' : '' }}">
                    <i class="fas fa-handshake w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Contratos</span>
                </a>
                
                <a href="{{ route('partner.featured') }}" class="flex items-center px-6 py-3 hover:bg-blue-700 {{ request()->routeIs('partner.featured') ? 'bg-blue-700 border-l-4 border-white' : '' }}">
                    <i class="fas fa-star w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Destaque</span>
                </a>
                
                <a href="{{ route('partner.wallet') }}" class="flex items-center px-6 py-3 hover:bg-blue-700 {{ request()->routeIs('partner.wallet') ? 'bg-blue-700 border-l-4 border-white' : '' }}">
                    <i class="fas fa-wallet w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Carteira</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-2xl font-semibold text-gray-800">@yield('page-title')</h2>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Saldo -->
                        <div class="bg-gradient-to-r from-green-400 to-green-600 text-white px-4 py-2 rounded-lg">
                            <span class="text-sm">Saldo:</span>
                            <span class="font-bold ml-2">R$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2, ',', '.') }}</span>
                        </div>
                        
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2">
                                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=3B82F6&color=fff" class="w-10 h-10 rounded-full">
                                <div class="text-left">
                                    <div class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500">Proprietário</div>
                                </div>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                                <a href="{{ route('partner.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Perfil
                                </a>
                                <a href="{{ route('partner.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Configurações
                                </a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Sair
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
