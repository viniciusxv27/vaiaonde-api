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
<body class="bg-gray-50">
    <div class="flex h-screen" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="bg-black text-white transition-all duration-300 shadow-xl">
            <div class="p-4 flex items-center justify-between border-b border-gray-800">
                <div x-show="sidebarOpen" class="flex items-center">
                    <img src="{{ asset('logo.png') }}" alt="VaiAonde" class="h-8">
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-[#FEB800] hover:text-white transition">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="mt-8">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-gray-900 transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 border-l-4 border-[#FEB800]' : '' }}">
                    <i class="fas fa-home w-6 {{ request()->routeIs('admin.dashboard') ? 'text-[#FEB800]' : '' }}"></i>
                    <span x-show="sidebarOpen" class="ml-3">Dashboard</span>
                </a>
                
                <a href="{{ route('admin.users') }}" class="flex items-center px-6 py-3 hover:bg-gray-900 transition {{ request()->routeIs('admin.users*') ? 'bg-gray-900 border-l-4 border-[#FEB800]' : '' }}">
                    <i class="fas fa-users w-6 {{ request()->routeIs('admin.users*') ? 'text-[#FEB800]' : '' }}"></i>
                    <span x-show="sidebarOpen" class="ml-3">Usuários</span>
                </a>
                
                <a href="{{ route('admin.places') }}" class="flex items-center px-6 py-3 hover:bg-gray-900 transition {{ request()->routeIs('admin.places*') ? 'bg-gray-900 border-l-4 border-[#FEB800]' : '' }}">
                    <i class="fas fa-map-marker-alt w-6 {{ request()->routeIs('admin.places*') ? 'text-[#FEB800]' : '' }}"></i>
                    <span x-show="sidebarOpen" class="ml-3">Lugares</span>
                </a>
                
                <a href="{{ route('admin.videos') }}" class="flex items-center px-6 py-3 hover:bg-gray-900 transition {{ request()->routeIs('admin.videos*') ? 'bg-gray-900 border-l-4 border-[#FEB800]' : '' }}">
                    <i class="fas fa-video w-6 {{ request()->routeIs('admin.videos*') ? 'text-[#FEB800]' : '' }}"></i>
                    <span x-show="sidebarOpen" class="ml-3">Vídeos</span>
                </a>
                
                <a href="{{ route('admin.proposals') }}" class="flex items-center px-6 py-3 hover:bg-gray-900 transition {{ request()->routeIs('admin.proposals*') ? 'bg-gray-900 border-l-4 border-[#FEB800]' : '' }}">
                    <i class="fas fa-handshake w-6 {{ request()->routeIs('admin.proposals*') ? 'text-[#FEB800]' : '' }}"></i>
                    <span x-show="sidebarOpen" class="ml-3">Propostas</span>
                </a>
                
                <a href="{{ route('admin.transactions') }}" class="flex items-center px-6 py-3 hover:bg-gray-900 transition {{ request()->routeIs('admin.transactions*') ? 'bg-gray-900 border-l-4 border-[#FEB800]' : '' }}">
                    <i class="fas fa-money-bill-wave w-6 {{ request()->routeIs('admin.transactions*') ? 'text-[#FEB800]' : '' }}"></i>
                    <span x-show="sidebarOpen" class="ml-3">Transações</span>
                </a>
                
                <a href="{{ route('admin.banners') }}" class="flex items-center px-6 py-3 hover:bg-gray-900 transition {{ request()->routeIs('admin.banners*') ? 'bg-gray-900 border-l-4 border-[#FEB800]' : '' }}">
                    <i class="fas fa-image w-6 {{ request()->routeIs('admin.banners*') ? 'text-[#FEB800]' : '' }}"></i>
                    <span x-show="sidebarOpen" class="ml-3">Banners</span>
                </a>
                
                <a href="{{ route('admin.subscriptions') }}" class="flex items-center px-6 py-3 hover:bg-gray-900 transition {{ request()->routeIs('admin.subscriptions*') ? 'bg-gray-900 border-l-4 border-[#FEB800]' : '' }}">
                    <i class="fas fa-crown w-6 {{ request()->routeIs('admin.subscriptions*') ? 'text-[#FEB800]' : '' }}"></i>
                    <span x-show="sidebarOpen" class="ml-3">Assinaturas</span>
                </a>
                
                <a href="{{ route('admin.cities') }}" class="flex items-center px-6 py-3 hover:bg-gray-900 transition {{ request()->routeIs('admin.cities*') ? 'bg-gray-900 border-l-4 border-[#FEB800]' : '' }}">
                    <i class="fas fa-city w-6 {{ request()->routeIs('admin.cities*') ? 'text-[#FEB800]' : '' }}"></i>
                    <span x-show="sidebarOpen" class="ml-3">Cidades</span>
                </a>
                
                <a href="{{ route('admin.categories') }}" class="flex items-center px-6 py-3 hover:bg-gray-900 transition {{ request()->routeIs('admin.categories*') ? 'bg-gray-900 border-l-4 border-[#FEB800]' : '' }}">
                    <i class="fas fa-tags w-6 {{ request()->routeIs('admin.categories*') ? 'text-[#FEB800]' : '' }}"></i>
                    <span x-show="sidebarOpen" class="ml-3">Categorias</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-md border-b-4 border-[#FEB800]">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-2xl font-bold text-black">@yield('page-title')</h2>
                    
                    <div class="flex items-center space-x-4">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                                <img src="https://ui-avatars.com/api/?name=Admin&background=FEB800&color=000" class="w-10 h-10 rounded-full border-2 border-[#FEB800]">
                                <span class="font-medium">Admin</span>
                                <i class="fas fa-chevron-down text-sm text-[#FEB800]"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border-2 border-[#FEB800] py-1 z-10">
                                <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FEB800] hover:text-black transition">
                                    <i class="fas fa-user mr-2"></i>Perfil
                                </a>
                                <a href="{{ route('admin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#FEB800] hover:text-black transition">
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
                    <div class="bg-[#FEB800] bg-opacity-10 border-l-4 border-[#FEB800] text-black px-4 py-3 rounded mb-4 flex items-center">
                        <i class="fas fa-check-circle text-[#FEB800] mr-3 text-xl"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
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
