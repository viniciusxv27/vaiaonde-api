<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <title>@yield('title') - VaiAonde Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="bg-gray-900 text-white transition-all duration-300">
            <div class="p-4 flex items-center justify-between">
                <div x-show="sidebarOpen" class="flex items-center">
                    <img src="{{ asset('logo.png') }}" alt="VaiAonde" class="h-8">
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-white">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="mt-8">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-home w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Dashboard</span>
                </a>
                
                <a href="{{ route('admin.users') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 {{ request()->routeIs('admin.users*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-users w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Usuários</span>
                </a>
                
                <a href="{{ route('admin.places') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 {{ request()->routeIs('admin.places*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-map-marker-alt w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Lugares</span>
                </a>
                
                <a href="{{ route('admin.videos') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 {{ request()->routeIs('admin.videos*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-video w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Vídeos</span>
                </a>
                
                <a href="{{ route('admin.proposals') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 {{ request()->routeIs('admin.proposals*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-handshake w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Propostas</span>
                </a>
                
                <a href="{{ route('admin.transactions') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 {{ request()->routeIs('admin.transactions*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-money-bill-wave w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Transações</span>
                </a>
                
                <a href="{{ route('admin.banners') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 {{ request()->routeIs('admin.banners*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-image w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Banners</span>
                </a>
                
                <a href="{{ route('admin.subscriptions') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 {{ request()->routeIs('admin.subscriptions*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-crown w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Assinaturas</span>
                </a>
                
                <a href="{{ route('admin.cities') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 {{ request()->routeIs('admin.cities*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-city w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Cidades</span>
                </a>
                
                <a href="{{ route('admin.categories') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 {{ request()->routeIs('admin.categories*') ? 'bg-gray-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-tags w-6"></i>
                    <span x-show="sidebarOpen" class="ml-3">Categorias</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-2xl font-semibold text-gray-800">@yield('page-title')</h2>
                    
                    <div class="flex items-center space-x-4">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2">
                                <img src="https://ui-avatars.com/api/?name=Admin&background=3B82F6&color=fff" class="w-10 h-10 rounded-full">
                                <span class="text-gray-700">Admin</span>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            
                                                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                                <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Perfil
                                </a>
                                <a href="{{ route('admin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
