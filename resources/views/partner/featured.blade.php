@extends('layouts.partner')

@section('title', 'Destaque')
@section('page-title', 'Destaque no App')

@section('content')
<div class="space-y-6">
    <!-- Featured Info Banner -->
    <div class="bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 rounded-lg shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold flex items-center">
                    <i class="fas fa-star mr-3"></i>
                    Destaque no App
                </h2>
                <p class="mt-2 text-lg">Apareça na tela principal para milhares de usuários!</p>
                <div class="mt-4 flex items-center space-x-6 text-sm">
                    <div>
                        <i class="fas fa-clock mr-2"></i>
                        <span>30 dias de exposição</span>
                    </div>
                    <div>
                        <i class="fas fa-users mr-2"></i>
                        <span>Alcance até 50.000 usuários</span>
                    </div>
                    <div>
                        <i class="fas fa-chart-line mr-2"></i>
                        <span>Aumento médio de 300% nas visualizações</span>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <p class="text-5xl font-bold">R$ {{ number_format($featuredPrice ?? 39.90, 2, ',', '.') }}</p>
                <p class="text-sm opacity-90">por 30 dias</p>
            </div>
        </div>
    </div>

    <!-- Active Featured Places -->
    @if(isset($activeFeatured) && $activeFeatured->count() > 0)
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold flex items-center">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                Lugares em Destaque Ativos
            </h3>
        </div>
        <div class="divide-y">
            @foreach($activeFeatured as $featured)
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-yellow-100 rounded-full p-4">
                            <i class="fas fa-star text-yellow-600 text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg">{{ $featured->place->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $featured->place->city->name ?? 'Cidade' }}</p>
                            <div class="mt-2 flex items-center space-x-4 text-sm text-gray-600">
                                <span>
                                    <i class="fas fa-calendar-check text-green-600"></i>
                                    Início: {{ $featured->starts_at->format('d/m/Y') }}
                                </span>
                                <span>
                                    <i class="fas fa-calendar-times text-red-600"></i>
                                    Término: {{ $featured->ends_at->format('d/m/Y') }}
                                </span>
                                <span>
                                    <i class="fas fa-clock text-blue-600"></i>
                                    {{ $featured->ends_at->diffInDays(now()) }} dias restantes
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg font-semibold">
                            <i class="fas fa-check"></i> Ativo
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- My Places -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Selecione um Lugar para Destacar</h3>
            <p class="text-sm text-gray-500 mt-1">Escolha qual estabelecimento você quer promover no app</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            @forelse($places ?? [] as $place)
            <div class="border rounded-lg p-6 hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="font-semibold text-lg">{{ $place->name }}</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-map-marker-alt"></i> {{ $place->address ?? 'Endereço não cadastrado' }}
                        </p>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-city"></i> {{ $place->city->name ?? 'Cidade' }}
                        </p>
                        
                        @php
                            $isFeatured = isset($activeFeatured) && $activeFeatured->where('place_id', $place->id)->count() > 0;
                        @endphp
                        
                        @if($isFeatured)
                        <div class="mt-4">
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">
                                <i class="fas fa-star"></i> Já está em destaque
                            </span>
                        </div>
                        @else
                        <div class="mt-4">
                            <form method="POST" action="{{ route('partner.featured.purchase') }}">
                                @csrf
                                <input type="hidden" name="place_id" value="{{ $place->id }}">
                                <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-yellow-600 transition">
                                    <i class="fas fa-star mr-2"></i>Destacar por R$ {{ number_format($featuredPrice ?? 39.90, 2, ',', '.') }}
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @if($place->image)
                    <img src="{{ $place->image }}" class="w-24 h-24 rounded-lg object-cover ml-4">
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-2 text-center py-12 text-gray-500">
                <i class="fas fa-store text-5xl mb-4"></i>
                <p class="text-lg">Você ainda não possui lugares cadastrados</p>
                <a href="{{ route('partner.places') }}" class="text-blue-600 hover:text-blue-700 mt-2 inline-block">
                    Cadastrar meu primeiro lugar
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Benefits -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-eye text-blue-600 text-2xl"></i>
            </div>
            <h4 class="font-semibold text-lg mb-2">Maior Visibilidade</h4>
            <p class="text-sm text-gray-600">Seu estabelecimento aparece na tela principal do app para todos os usuários</p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-chart-line text-green-600 text-2xl"></i>
            </div>
            <h4 class="font-semibold text-lg mb-2">Mais Clientes</h4>
            <p class="text-sm text-gray-600">Aumente em até 300% o número de pessoas que visitam seu perfil</p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users text-purple-600 text-2xl"></i>
            </div>
            <h4 class="font-semibold text-lg mb-2">Alcance Premium</h4>
            <p class="text-sm text-gray-600">Seja visto por milhares de usuários que buscam experiências na sua cidade</p>
        </div>
    </div>

    <!-- FAQ -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Perguntas Frequentes</h3>
        </div>
        <div class="divide-y" x-data="{ open: null }">
            <div class="p-6">
                <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between text-left">
                    <h4 class="font-medium">Como funciona o destaque?</h4>
                    <i class="fas fa-chevron-down transition" :class="open === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 1" class="mt-4 text-sm text-gray-600">
                    Ao contratar o destaque, seu estabelecimento aparecerá na tela principal do app por 30 dias corridos, com posição privilegiada nos resultados de busca e maior visibilidade para os usuários.
                </div>
            </div>
            
            <div class="p-6">
                <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between text-left">
                    <h4 class="font-medium">Posso cancelar antes dos 30 dias?</h4>
                    <i class="fas fa-chevron-down transition" :class="open === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 2" class="mt-4 text-sm text-gray-600">
                    O período de destaque é fixo de 30 dias. Não há reembolso parcial, mas você pode optar por não renovar ao final do período.
                </div>
            </div>
            
            <div class="p-6">
                <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between text-left">
                    <h4 class="font-medium">Quantos lugares posso destacar ao mesmo tempo?</h4>
                    <i class="fas fa-chevron-down transition" :class="open === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 3" class="mt-4 text-sm text-gray-600">
                    Você pode destacar quantos lugares quiser! Cada lugar terá seu próprio período de 30 dias de destaque.
                </div>
            </div>
            
            <div class="p-6">
                <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between text-left">
                    <h4 class="font-medium">Como é feito o pagamento?</h4>
                    <i class="fas fa-chevron-down transition" :class="open === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 4" class="mt-4 text-sm text-gray-600">
                    O pagamento é debitado automaticamente do saldo da sua carteira. Certifique-se de ter saldo suficiente antes de contratar o destaque.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
