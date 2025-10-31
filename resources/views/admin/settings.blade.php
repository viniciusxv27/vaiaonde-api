@extends('layouts.admin')

@section('title', 'Configurações')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Configurações Gerais -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Configurações Gerais</h2>
        
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nome da Plataforma</label>
                <input 
                    type="text" 
                    name="app_name" 
                    value="{{ old('app_name', $settings['app_name'] ?? config('app.name')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">E-mail de Contato</label>
                <input 
                    type="email" 
                    name="contact_email" 
                    value="{{ old('contact_email', $settings['contact_email'] ?? 'contato@vaiaonde.com') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-4">Suporte e Ajuda</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">E-mail de Ajuda</label>
                        <input 
                            type="email" 
                            name="help_email" 
                            value="{{ old('help_email', $settings['help_email'] ?? 'ajuda@vaiaonde.com.br') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="ajuda@vaiaonde.com.br"
                        >
                        <p class="text-xs text-gray-500 mt-1">Exibido nos menus de Proprietário e Influenciador</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">WhatsApp de Ajuda</label>
                        <input 
                            type="text" 
                            name="help_whatsapp" 
                            value="{{ old('help_whatsapp', $settings['help_whatsapp'] ?? '5511999999999') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="5511999999999"
                        >
                        <p class="text-xs text-gray-500 mt-1">Formato: DDI + DDD + Número (sem espaços)</p>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-4">Preços e Limites</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Preço Destaque (30 dias)</label>
                        <div class="flex items-center">
                            <span class="px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg">R$</span>
                            <input 
                                type="number" 
                                name="featured_price" 
                                value="{{ old('featured_price', $settings['featured_price'] ?? '39.90') }}"
                                step="0.01"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Saque Mínimo</label>
                        <div class="flex items-center">
                            <span class="px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg">R$</span>
                            <input 
                                type="number" 
                                name="min_withdrawal" 
                                value="{{ old('min_withdrawal', $settings['min_withdrawal'] ?? '20.00') }}"
                                step="0.01"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-blue-500"
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
                    <i class="fas fa-save mr-2"></i>Salvar Configurações
                </button>
            </div>
        </form>
    </div>
    
    <!-- Manutenção -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Manutenção</h2>
        
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <h3 class="font-semibold">Modo Manutenção</h3>
                    <p class="text-sm text-gray-600">Desabilita o acesso à plataforma para usuários</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <h3 class="font-semibold">Limpar Cache</h3>
                    <p class="text-sm text-gray-600">Remove arquivos de cache do sistema</p>
                </div>
                <form method="POST" action="{{ route('admin.cache.clear') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-trash mr-2"></i>Limpar
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Estatísticas do Sistema -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Estatísticas do Sistema</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-blue-50 rounded-lg">
                <div class="text-sm text-blue-600 font-semibold">Total de Usuários</div>
                <div class="text-3xl font-bold text-blue-700 mt-2">{{ \App\Models\User::count() }}</div>
            </div>
            
            <div class="p-4 bg-green-50 rounded-lg">
                <div class="text-sm text-green-600 font-semibold">Total de Lugares</div>
                <div class="text-3xl font-bold text-green-700 mt-2">{{ \App\Models\Place::count() }}</div>
            </div>
            
            <div class="p-4 bg-purple-50 rounded-lg">
                <div class="text-sm text-purple-600 font-semibold">Total de Vídeos</div>
                <div class="text-3xl font-bold text-purple-700 mt-2">{{ \App\Models\Video::count() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
