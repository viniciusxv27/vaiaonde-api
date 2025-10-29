@extends('layouts.partner')

@section('title', 'Meus Lugares')

@section('content')
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fas fa-store text-[#FEB800]"></i>
                Meus Estabelecimentos
            </h1>
            <p class="text-gray-600 mt-2">Gerencie seus lugares, restaurantes e eventos cadastrados</p>
        </div>
        <a href="{{ route('partner.places.create') }}" class="bg-gradient-to-r from-[#FEB800] to-[#ff9500] hover:from-[#ff9500] hover:to-[#FEB800] text-black px-6 py-3 rounded-lg font-bold transition-all duration-200 shadow-lg hover:shadow-xl inline-flex items-center justify-center">
            <i class="fas fa-plus mr-2"></i>Novo Estabelecimento
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 text-green-800 p-4 mb-6 rounded-lg shadow-sm" role="alert">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
            <p class="font-semibold">{{ session('success') }}</p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($places as $place)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300 border border-gray-100">
        <!-- Imagem Principal -->
        <div class="relative h-52 overflow-hidden bg-gradient-to-br from-[#FEB800] to-[#ff9500]">
            @if($place->card_image)
                <img src="{{ asset($place->card_image) }}" alt="{{ $place->name }}" class="w-full h-full object-cover">
            @elseif($place->primaryImage)
                <img src="{{ asset($place->primaryImage->image_path) }}" alt="{{ $place->name }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-map-marker-alt text-white text-6xl opacity-50"></i>
                </div>
            @endif
            
            <!-- Logo Overlay -->
            @if($place->logo)
                <div class="absolute bottom-3 left-3 w-16 h-16 bg-white rounded-full shadow-lg border-4 border-white overflow-hidden">
                    <img src="{{ asset($place->logo) }}" alt="Logo {{ $place->name }}" class="w-full h-full object-cover">
                </div>
            @endif
            
            <!-- Badge Status -->
            <div class="absolute top-3 right-3">
                @if($place->is_active)
                    <span class="px-3 py-1 text-xs bg-green-500 text-white rounded-full font-semibold shadow-lg">
                        <i class="fas fa-check-circle mr-1"></i>Ativo
                    </span>
                @else
                    <span class="px-3 py-1 text-xs bg-red-500 text-white rounded-full font-semibold shadow-lg">
                        <i class="fas fa-times-circle mr-1"></i>Inativo
                    </span>
                @endif
            </div>
            
            <!-- Badge Tipo -->
            <div class="absolute top-3 left-3">
                <span class="px-3 py-1 text-xs bg-black bg-opacity-60 text-white rounded-full font-semibold backdrop-blur-sm">
                    <i class="fas fa-tag mr-1"></i>{{ ucfirst($place->type) }}
                </span>
            </div>
        </div>
        
        <!-- Conteúdo do Card -->
        <div class="p-5">
            <!-- Título e Localização -->
            <div class="mb-4">
                <h3 class="text-xl font-bold text-gray-800 mb-2 line-clamp-1">{{ $place->name }}</h3>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-map-marker-alt mr-2 text-[#FEB800]"></i>
                    <span class="line-clamp-1">{{ $place->city->name ?? 'Localização não definida' }}</span>
                </div>
            </div>
            
            <!-- Estatísticas -->
            <div class="grid grid-cols-3 gap-3 py-4 border-t border-b border-gray-100">
                <div class="text-center">
                    <div class="text-xl font-bold text-blue-600">
                        {{ \App\Models\Video::where('place_id', $place->id)->count() }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Vídeos</div>
                </div>
                
                <div class="text-center border-l border-r border-gray-100">
                    <div class="text-xl font-bold text-purple-600">
                        {{ number_format(\App\Models\Video::where('place_id', $place->id)->sum('views_count')) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Views</div>
                </div>
                
                <div class="text-center">
                    <div class="text-xl font-bold text-[#FEB800] flex items-center justify-center gap-1">
                        <i class="fas fa-star text-sm"></i>
                        {{ $place->rating ? number_format($place->rating, 1) : '0.0' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Avaliação</div>
                </div>
            </div>
            
            <!-- Badge Assinatura -->
            @if($place->subscription && $place->subscription->isActive())
            <div class="mt-4 bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-purple-700 flex items-center">
                        <i class="fas fa-crown mr-2 text-[#FEB800]"></i>
                        {{ $place->subscription->plan->name }}
                    </span>
                    <span class="text-xs text-purple-600">
                        até {{ $place->subscription->ends_at->format('d/m/Y') }}
                    </span>
                </div>
            </div>
            @endif
            
            <!-- Galeria de Imagens (Preview) -->
            @if($place->images && $place->images->count() > 0)
            <div class="mt-4">
                <div class="flex gap-2 overflow-x-auto pb-2">
                    @foreach($place->images->take(4) as $image)
                        <div class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200 hover:border-[#FEB800] transition">
                            <img src="{{ asset($image->image_path) }}" alt="Imagem {{ $loop->iteration }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                    @if($place->images->count() > 4)
                        <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 text-xs font-semibold">
                            +{{ $place->images->count() - 4 }}
                        </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Botões de Ação -->
            <div class="mt-5 flex gap-2">
                <a href="{{ route('partner.places.edit', $place->id) }}" class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2.5 rounded-lg text-center text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md">
                    <i class="fas fa-edit mr-1"></i>Editar
                </a>
                <form action="{{ route('partner.places.delete', $place->id) }}" method="POST" class="flex-1" onsubmit="return confirm('⚠️ Tem certeza que deseja excluir {{ $place->name }}?\n\nEsta ação não pode ser desfeita!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md">
                        <i class="fas fa-trash mr-1"></i>Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 bg-white rounded-xl shadow-sm p-16 text-center">
        <div class="max-w-md mx-auto">
            <div class="w-24 h-24 bg-gradient-to-br from-[#FEB800] to-[#ff9500] rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-store text-white text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-3">Nenhum estabelecimento cadastrado</h3>
            <p class="text-gray-600 mb-6">Comece cadastrando seu primeiro estabelecimento gratuitamente e alcance milhares de pessoas!</p>
            <a href="{{ route('partner.places.create') }}" class="inline-block bg-gradient-to-r from-[#FEB800] to-[#ff9500] hover:from-[#ff9500] hover:to-[#FEB800] text-black px-8 py-4 rounded-lg font-bold transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-plus mr-2"></i>Cadastrar Meu Primeiro Estabelecimento
            </a>
        </div>
    </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $places->links() }}
</div>
@endsection
