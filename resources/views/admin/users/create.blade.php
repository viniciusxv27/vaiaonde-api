@extends('layouts.admin')

@section('title', 'Criar Usuário')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i>Voltar
    </a>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Criar Novo Usuário</h2>
    
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nome</label>
                <input 
                    type="text" 
                    name="name" 
                    value="{{ old('name') }}"
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
                    value="{{ old('email') }}"
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Senha</label>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Role</label>
                <select name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="comum" {{ old('role') == 'comum' ? 'selected' : '' }}>Comum</option>
                    <option value="assinante" {{ old('role') == 'assinante' ? 'selected' : '' }}>Assinante</option>
                    <option value="proprietario" {{ old('role') == 'proprietario' ? 'selected' : '' }}>Proprietário</option>
                    <option value="influenciador" {{ old('role') == 'influenciador' ? 'selected' : '' }}>Influenciador</option>
                </select>
                @error('role')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Telefone</label>
                <input 
                    type="text" 
                    name="phone" 
                    value="{{ old('phone') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Saldo Inicial da Carteira</label>
                <input 
                    type="number" 
                    name="wallet_balance" 
                    value="{{ old('wallet_balance', 0) }}"
                    step="0.01"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
        </div>
        
        <div class="flex items-center">
            <input 
                type="checkbox" 
                name="is_admin" 
                id="is_admin"
                {{ old('is_admin') ? 'checked' : '' }}
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2"
            >
            <label for="is_admin" class="text-gray-700 text-sm font-semibold">Admin (acesso ao painel administrativo)</label>
        </div>
        
        <div class="flex gap-4 pt-4">
            <button 
                type="submit" 
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                <i class="fas fa-save mr-2"></i>Criar Usuário
            </button>
            <a 
                href="{{ route('admin.users') }}" 
                class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
