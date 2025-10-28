@extends('layouts.admin')

@section('title', 'Meu Perfil')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Meu Perfil</h2>
        
        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="flex items-center space-x-6 pb-6 border-b">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3B82F6&color=fff&size=128" 
                     class="w-32 h-32 rounded-full">
                <div>
                    <h3 class="text-xl font-semibold">{{ auth()->user()->name }}</h3>
                    <p class="text-gray-600">{{ auth()->user()->email }}</p>
                    <span class="inline-block mt-2 px-3 py-1 bg-red-100 text-red-800 text-sm rounded-full">
                        Administrador
                    </span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nome</label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name', auth()->user()->name) }}"
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">E-mail</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="{{ old('email', auth()->user()->email) }}"
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Telefone</label>
                <input 
                    type="text" 
                    name="phone" 
                    value="{{ old('phone', auth()->user()->phone) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
            
            <div class="pt-4 border-t">
                <h3 class="text-lg font-semibold mb-4">Alterar Senha</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Senha Atual</label>
                        <input 
                            type="password" 
                            name="current_password" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Nova Senha</label>
                            <input 
                                type="password" 
                                name="password" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Confirmar Nova Senha</label>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-4 pt-4">
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    <i class="fas fa-save mr-2"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
