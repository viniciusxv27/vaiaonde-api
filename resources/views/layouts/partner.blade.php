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
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="bg-[#FEB800] text-black transition-all duration-300 shadow-xl">
            <div class="p-4 flex items-center justify-between border-b-2 border-black border-opacity-10">
                <div x-show="sidebarOpen" class="flex items-center">
                    <img src="{{ asset('logo.png') }}" alt="VaiAonde" class="h-8">
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-black hover:text-white transition bg-black bg-opacity-10 hover:bg-opacity-20 p-2 rounded">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="mt-8">
                <a href="{{ route('partner.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-black hover:bg-opacity-10 transition {{ request()->routeIs('partner.dashboard') ? 'bg-black bg-opacity-10 border-l-4 border-black font-bold' : '' }}">
                    <i class="fas fa-chart-line w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Dashboard</span>
                </a>
                
                <a href="{{ route('partner.places') }}" class="flex items-center px-6 py-3 hover:bg-black hover:bg-opacity-10 transition {{ request()->routeIs('partner.places') ? 'bg-black bg-opacity-10 border-l-4 border-black font-bold' : '' }}">
                    <i class="fas fa-store w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Meus Lugares</span>
                </a>
                
                <a href="{{ route('partner.videos') }}" class="flex items-center px-6 py-3 hover:bg-black hover:bg-opacity-10 transition {{ request()->routeIs('partner.videos') ? 'bg-black bg-opacity-10 border-l-4 border-black font-bold' : '' }}">
                    <i class="fas fa-video w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Vídeos</span>
                </a>
                
                <a href="{{ route('partner.proposals') }}" class="flex items-center px-6 py-3 hover:bg-black hover:bg-opacity-10 transition {{ request()->routeIs('partner.proposals') ? 'bg-black bg-opacity-10 border-l-4 border-black font-bold' : '' }}">
                    <i class="fas fa-handshake w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Contratos</span>
                </a>
                
                <a href="{{ route('partner.featured') }}" class="flex items-center px-6 py-3 hover:bg-black hover:bg-opacity-10 transition {{ request()->routeIs('partner.featured') ? 'bg-black bg-opacity-10 border-l-4 border-black font-bold' : '' }}">
                    <i class="fas fa-star w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Destaque</span>
                </a>
                
                <a href="{{ route('partner.wallet') }}" class="flex items-center px-6 py-3 hover:bg-black hover:bg-opacity-10 transition {{ request()->routeIs('partner.wallet') ? 'bg-black bg-opacity-10 border-l-4 border-black font-bold' : '' }}">
                    <i class="fas fa-wallet w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Carteira</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-md border-b-4 border-black">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-2xl font-bold text-black">@yield('page-title')</h2>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Saldo -->
                        <div class="bg-black text-white px-6 py-3 rounded-lg shadow-lg border-2 border-[#FEB800]">
                            <span class="text-sm">Saldo:</span>
                            <span class="font-bold ml-2 text-[#FEB800]">R$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2, ',', '.') }}</span>
                        </div>
                        
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 bg-[#FEB800] text-black px-4 py-2 rounded-lg hover:bg-black hover:text-white border-2 border-black transition">
                                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=000000&color=FEB800" class="w-10 h-10 rounded-full border-2 border-black">
                                <div class="text-left">
                                    <div class="text-sm font-bold">{{ auth()->user()->name }}</div>
                                    <div class="text-xs opacity-75">Proprietário</div>
                                </div>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border-2 border-[#FEB800] py-1 z-10">
                                <a href="{{ route('partner.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FEB800] hover:text-black transition">
                                    <i class="fas fa-user mr-2"></i>Perfil
                                </a>
                                <a href="{{ route('partner.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FEB800] hover:text-black transition">
                                    <i class="fas fa-cog mr-2"></i>Configurações
                                </a>
                                <hr class="my-1 border-gray-200">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
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
                    <div class="bg-[#FEB800] bg-opacity-10 border-l-4 border-[#FEB800] text-black px-4 py-3 rounded mb-4 flex items-center shadow">
                        <i class="fas fa-check-circle text-[#FEB800] mr-3 text-xl"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4 flex items-center shadow">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3 text-xl"></i>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
