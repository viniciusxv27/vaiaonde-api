<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <title>Login - VaiAonde Capixaba</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .vai-aonde-gradient {
            background: linear-gradient(135deg, #000000 0%, #FEB800 100%);
        }
        .vai-aonde-btn {
            background: linear-gradient(135deg, #FEB800 0%, #000000 100%);
        }
        .vai-aonde-btn:hover {
            background: linear-gradient(135deg, #ffcc33 0%, #1a1a1a 100%);
        }
    </style>
</head>
<body class="vai-aonde-gradient min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="vai-aonde-gradient p-8 text-white text-center">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('logo.png') }}" alt="VaiAonde Capixaba" class="h-16">
            </div>
            <h1 class="text-2xl font-bold mb-1">VaiAonde Capixaba</h1>
            <p class="text-yellow-200">Painel Administrativo</p>
        </div>
        
        <!-- Form -->
        <div class="p-8">
            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
            @endif
            
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
            @endif
            
            <form method="POST" action="{{ route('login.submit') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-envelope mr-2 text-yellow-600"></i>E-mail
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                        placeholder="seu@email.com"
                        value="{{ old('email') }}"
                    >
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-lock mr-2 text-yellow-600"></i>Senha
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                        placeholder="••••••••"
                    >
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500">
                        <span class="ml-2 text-sm text-gray-600">Lembrar-me</span>
                    </label>
                    <a href="#" class="text-sm text-yellow-600 hover:text-yellow-700 font-medium">Esqueci minha senha</a>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full vai-aonde-btn text-white py-3 rounded-lg font-bold transition transform hover:scale-105 shadow-lg"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>Entrar
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t text-center">
                <p class="text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Você será redirecionado automaticamente de acordo com o seu perfil
                </p>
            </div>
        </div>
    </div>
</body>
</html>
