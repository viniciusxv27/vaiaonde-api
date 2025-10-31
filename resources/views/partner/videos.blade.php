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
        @if($video->thumbnail_url)
        <div class="relative cursor-pointer group" onclick="openVideoPlayer('{{ $video->video_url }}', '{{ addslashes($video->title ?? 'Sem título') }}')">
            <img src="{{ $video->thumbnail_url }}" alt="Thumbnail" class="w-full h-48 object-cover">
            
            <!-- Badge de Impulsionado -->
            @php
                $activeBoost = $video->boosts->first();
            @endphp
            @if($activeBoost && $activeBoost->isActive())
            <div class="absolute top-2 left-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg flex items-center gap-1">
                <i class="fas fa-rocket"></i>
                Impulsionado
            </div>
            @endif
            
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition flex items-center justify-center">
                <div class="transform scale-0 group-hover:scale-100 transition">
                    <i class="fas fa-play-circle text-white text-5xl"></i>
                </div>
            </div>
            <div class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                {{ gmdate("i:s", $video->duration ?? 0) }}
            </div>
        </div>
        @else
        <div class="w-full h-48 bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center cursor-pointer group relative" onclick="openVideoPlayer('{{ $video->video_url }}', '{{ addslashes($video->title ?? 'Sem título') }}')">
            <!-- Badge de Impulsionado -->
            @php
                $activeBoost = $video->boosts->first();
            @endphp
            @if($activeBoost && $activeBoost->isActive())
            <div class="absolute top-2 left-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg flex items-center gap-1">
                <i class="fas fa-rocket"></i>
                Impulsionado
            </div>
            @endif
            
            <div class="transform scale-100 group-hover:scale-110 transition">
                <i class="fas fa-play-circle text-white text-5xl"></i>
            </div>
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
            
            <!-- Métricas de Impulsionamento -->
            @if($activeBoost && $activeBoost->isActive())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-yellow-800">
                        <i class="fas fa-chart-line mr-1"></i>Métricas do Impulsionamento
                    </span>
                    <span class="text-xs text-yellow-600">
                        Termina em {{ $activeBoost->end_date->diffForHumans() }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-white rounded px-2 py-1">
                        <div class="text-gray-500">Impressões</div>
                        <div class="font-semibold text-gray-800">{{ number_format($activeBoost->impressions) }}</div>
                    </div>
                    <div class="bg-white rounded px-2 py-1">
                        <div class="text-gray-500">Cliques</div>
                        <div class="font-semibold text-gray-800">{{ number_format($activeBoost->clicks) }}</div>
                    </div>
                    <div class="bg-white rounded px-2 py-1">
                        <div class="text-gray-500">CTR</div>
                        <div class="font-semibold text-gray-800">{{ number_format($activeBoost->ctr, 2) }}%</div>
                    </div>
                    <div class="bg-white rounded px-2 py-1">
                        <div class="text-gray-500">Investido</div>
                        <div class="font-semibold text-gray-800">R$ {{ number_format($activeBoost->amount, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="flex items-center justify-between text-xs text-gray-500 mt-3 pt-3 border-t">
                <div class="flex items-center">
                    <i class="fas fa-eye mr-1"></i>
                    <span>{{ number_format($video->views_count ?? 0) }}</span>
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
            
            <!-- Boost Button -->
            @if(!$activeBoost || !$activeBoost->isActive())
            <button onclick="openBoostModal({{ $video->id }}, '{{ addslashes($video->title ?? 'Sem título') }}')" 
                    class="mt-3 w-full bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-rocket"></i>
                Impulsionar Vídeo
            </button>
            @else
            <div class="mt-3 w-full bg-gray-100 text-gray-500 py-2 px-4 rounded-lg text-center text-sm">
                <i class="fas fa-check-circle mr-1"></i>
                Vídeo Impulsionado
            </div>
            @endif
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

<!-- Video Player Modal -->
<div id="videoPlayerModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="relative max-w-4xl w-full">
        <button onclick="closeVideoPlayer()" class="absolute -top-10 right-0 text-white hover:text-gray-300">
            <i class="fas fa-times text-2xl"></i>
        </button>
        <div class="bg-black rounded-lg overflow-hidden">
            <video id="videoPlayer" class="w-full" controls autoplay>
                <source id="videoSource" src="" type="video/mp4">
                Seu navegador não suporta o elemento de vídeo.
            </video>
            <div class="p-4 bg-gray-900 text-white">
                <h3 id="videoTitle" class="font-semibold"></h3>
            </div>
        </div>
    </div>
</div>

<!-- Boost Modal -->
<div id="boostModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-rocket text-yellow-500 mr-2"></i>Impulsionar Vídeo
            </h3>
            <button onclick="closeBoostModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <p id="boostVideoTitle" class="text-gray-600 mb-4"></p>
        
        <form id="boostForm" method="POST" action="">
            @csrf
            <input type="hidden" id="boostVideoId" name="video_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Duração do Impulsionamento</label>
                <select name="duration_days" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500" required>
                    <option value="7">7 dias - R$ 50,00</option>
                    <option value="14">14 dias - R$ 90,00</option>
                    <option value="30">30 dias - R$ 150,00</option>
                </select>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    O vídeo será destacado na página inicial e terá maior visibilidade.
                </p>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeBoostModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-lg hover:from-yellow-600 hover:to-orange-600">
                    <i class="fas fa-rocket mr-2"></i>Impulsionar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openVideoPlayer(videoUrl, title) {
    const modal = document.getElementById('videoPlayerModal');
    const videoSource = document.getElementById('videoSource');
    const videoPlayer = document.getElementById('videoPlayer');
    const videoTitle = document.getElementById('videoTitle');
    
    videoSource.src = videoUrl;
    videoTitle.textContent = title;
    videoPlayer.load();
    modal.classList.remove('hidden');
}

function closeVideoPlayer() {
    const modal = document.getElementById('videoPlayerModal');
    const videoPlayer = document.getElementById('videoPlayer');
    
    videoPlayer.pause();
    modal.classList.add('hidden');
}

function openBoostModal(videoId, title) {
    const modal = document.getElementById('boostModal');
    const form = document.getElementById('boostForm');
    const videoIdInput = document.getElementById('boostVideoId');
    const videoTitleEl = document.getElementById('boostVideoTitle');
    
    videoIdInput.value = videoId;
    videoTitleEl.textContent = title;
    form.action = '{{ route("partner.videos.boost") }}';
    modal.classList.remove('hidden');
}

function closeBoostModal() {
    const modal = document.getElementById('boostModal');
    modal.classList.add('hidden');
}

// Close modals on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoPlayer();
        closeBoostModal();
    }
});

// Close modals on background click
document.getElementById('videoPlayerModal').addEventListener('click', function(e) {
    if (e.target === this) closeVideoPlayer();
});

document.getElementById('boostModal').addEventListener('click', function(e) {
    if (e.target === this) closeBoostModal();
});
</script>
@endsection
