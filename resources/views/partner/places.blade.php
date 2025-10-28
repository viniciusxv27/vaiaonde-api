@extends('layouts.partner')

@section('title', 'Meus Lugares')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Meus Estabelecimentos</h1>
        <p class="text-gray-600 mt-1">Gerencie seus lugares, restaurantes e eventos</p>
    </div>
    <a href="{{ route('partner.places.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition inline-flex items-center">
        <i class="fas fa-plus mr-2"></i>Cadastrar Estabelecimento
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
        <p>{{ session('success') }}</p>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($places as $place)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
        @if($place->image)
        <img src="{{ asset($place->image) }}" alt="{{ $place->name }}" class="w-full h-48 object-cover">
        @else
        <div class="w-full h-48 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
            <i class="fas fa-map-marker-alt text-white text-5xl"></i>
        </div>
        @endif
        
        <div class="p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-800">{{ $place->name }}</h3>
                @if(!$place->is_active)
                    <span class="px-2 py-1 text-xs bg-red-100 text-red-600 rounded-full">Inativo</span>
                @endif
            </div>
            
            <div class="flex items-center text-sm text-gray-600 mb-1">
                <i class="fas fa-tag mr-2"></i>
                <span class="capitalize">{{ $place->type }}</span>
            </div>
            
            <div class="flex items-center text-sm text-gray-600 mb-2">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <span>{{ $place->city->name ?? 'N/A' }}</span>
            </div>
            
            <div class="flex items-center justify-between mt-4 pt-4 border-t">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ \App\Models\Video::where('place_id', $place->id)->count() }}
                    </div>
                    <div class="text-xs text-gray-500">Menções</div>
                </div>
            
            <div class="flex items-center justify-between mt-4 pt-4 border-t">
                <div class="text-center flex-1">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ \App\Models\Video::where('place_id', $place->id)->count() }}
                    </div>
                    <div class="text-xs text-gray-500">Vídeos</div>
                </div>
                
                <div class="text-center flex-1">
                    <div class="text-2xl font-bold text-purple-600">
                        {{ number_format(\App\Models\Video::where('place_id', $place->id)->sum('views_count')) }}
                    </div>
                    <div class="text-xs text-gray-500">Visualizações</div>
                </div>
                
                <div class="text-center flex-1">
                    <div class="text-2xl font-bold text-green-600">
                        {{ \App\Models\Rating::where('place_id', $place->id)->avg('rating') ? number_format(\App\Models\Rating::where('place_id', $place->id)->avg('rating'), 1) : '0.0' }}
                    </div>
                    <div class="text-xs text-gray-500">Avaliação</div>
                </div>
            </div>
            
            @if($place->subscription)
            <div class="mt-3 bg-purple-50 border border-purple-200 rounded-lg p-2 text-center">
                <span class="text-xs text-purple-700">
                    <i class="fas fa-crown mr-1"></i>
                    {{ $place->subscription->plan->name }}
                </span>
            </div>
            @endif
            
            <div class="mt-4 flex gap-2">
                <a href="{{ route('partner.places.edit', $place->id) }}" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-center text-sm font-medium transition">
                    <i class="fas fa-edit mr-1"></i>Editar
                </a>
                <form action="{{ route('partner.places.delete', $place->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Tem certeza que deseja excluir este estabelecimento?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-trash mr-1"></i>Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 bg-white rounded-lg shadow-sm p-12 text-center">
        <i class="fas fa-store text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhum estabelecimento cadastrado</h3>
        <p class="text-gray-500 mb-4">Comece cadastrando seu primeiro estabelecimento gratuitamente!</p>
        <a href="{{ route('partner.places.create') }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition">
            <i class="fas fa-plus mr-2"></i>Cadastrar Agora
        </a>
    </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $places->links() }}
</div>
@endsection
