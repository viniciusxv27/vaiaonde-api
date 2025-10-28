@extends('layouts.partner')

@section('title', 'Menções em Vídeos')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Menções em Vídeos</h1>
    <p class="text-gray-600 mt-1">Vídeos que mencionam seus lugares</p>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" class="flex gap-4">
        <select name="place_id" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">Todos os lugares</option>
            @foreach(\App\Models\Place::where('owner_id', auth()->id())->get() as $place)
            <option value="{{ $place->id }}" {{ request('place_id') == $place->id ? 'selected' : '' }}>
                {{ $place->name }}
            </option>
            @endforeach
        </select>
        
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-filter mr-2"></i>Filtrar
        </button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($videos as $video)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
        @if($video->thumbnail)
        <div class="relative">
            <img src="{{ $video->thumbnail }}" alt="Thumbnail" class="w-full h-48 object-cover">
            <div class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                {{ gmdate("i:s", $video->duration ?? 0) }}
            </div>
        </div>
        @else
        <div class="w-full h-48 bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center">
            <i class="fas fa-video text-white text-5xl"></i>
        </div>
        @endif
        
        <div class="p-4">
            <div class="flex items-start gap-3 mb-3">
                @if($video->user->avatar)
                <img src="{{ $video->user->avatar }}" alt="{{ $video->user->name }}" class="w-10 h-10 rounded-full">
                @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                    <span class="text-white font-semibold">{{ substr($video->user->name, 0, 1) }}</span>
                </div>
                @endif
                
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800 text-sm line-clamp-2">{{ $video->title ?? 'Sem título' }}</h3>
                    <p class="text-xs text-gray-500">{{ $video->user->name }}</p>
                </div>
            </div>
            
            <div class="flex items-center text-sm text-gray-600 mb-2">
                <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                <span class="font-medium">{{ $video->place->name }}</span>
            </div>
            
            <div class="flex items-center justify-between text-xs text-gray-500 mt-3 pt-3 border-t">
                <div class="flex items-center">
                    <i class="fas fa-eye mr-1"></i>
                    <span>{{ number_format($video->views_count) }}</span>
                </div>
                
                <div class="flex items-center">
                    <i class="fas fa-heart mr-1"></i>
                    <span>{{ number_format($video->likes_count ?? 0) }}</span>
                </div>
                
                <div class="flex items-center">
                    <i class="fas fa-comment mr-1"></i>
                    <span>{{ number_format($video->comments_count ?? 0) }}</span>
                </div>
            </div>
            
            <div class="mt-3 text-xs text-gray-400">
                <i class="fas fa-clock mr-1"></i>
                {{ $video->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-4 bg-white rounded-lg shadow-sm p-12 text-center">
        <i class="fas fa-video-slash text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhum vídeo encontrado</h3>
        <p class="text-gray-500">Ainda não há vídeos mencionando seus lugares</p>
    </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $videos->links() }}
</div>
@endsection
