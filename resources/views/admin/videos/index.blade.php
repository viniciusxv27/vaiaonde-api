@extends('layouts.admin')

@section('title', 'Vídeos')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Vídeos</h1>
    <p class="text-gray-600 mt-1">Todos os vídeos publicados na plataforma</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    @forelse($videos as $video)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="relative cursor-pointer" onclick="openVideoPlayer('{{ $video->video_url }}', '{{ addslashes($video->title) }}')">
            @if($video->thumbnail_url)
            <img src="{{ $video->thumbnail_url }}" alt="Thumbnail" class="w-full h-40 object-cover">
            @else
            <div class="w-full h-40 bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center">
                <i class="fas fa-video text-white text-4xl"></i>
            </div>
            @endif
            <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-30 transition flex items-center justify-center">
                <i class="fas fa-play-circle text-white text-4xl opacity-0 hover:opacity-100 transition"></i>
            </div>
            <div class="absolute top-2 right-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                {{ gmdate("i:s", $video->duration ?? 0) }}
            </div>
        </div>
        
        <div class="p-3">
            <h3 class="font-semibold text-sm text-gray-800 line-clamp-2 mb-2">
                {{ $video->title ?? 'Sem título' }}
            </h3>
            
            <div class="flex items-center text-xs text-gray-600 mb-2">
                <i class="fas fa-user mr-1"></i>
                <span>{{ $video->user->name }}</span>
            </div>
            
            @if($video->place)
            <div class="flex items-center text-xs text-blue-600 mb-2">
                <i class="fas fa-map-marker-alt mr-1"></i>
                <span>{{ $video->place->name }}</span>
            </div>
            @endif
            
            <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                <span><i class="fas fa-eye mr-1"></i>{{ number_format($video->views_count) }}</span>
                <span><i class="fas fa-heart mr-1"></i>{{ number_format($video->likes_count ?? 0) }}</span>
            </div>
            
            <div class="flex justify-between items-center mt-3 pt-3 border-t">
                <span class="text-xs text-gray-400">{{ $video->created_at->diffForHumans() }}</span>
                <form action="{{ route('admin.videos.delete', $video->id) }}" method="POST" onsubmit="return confirm('Tem certeza?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-4 bg-white rounded-lg shadow-sm p-12 text-center">
        <i class="fas fa-video-slash text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600">Nenhum vídeo encontrado</h3>
    </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $videos->links() }}
</div>

<!-- Video Player Modal -->
<div id="videoPlayerModal" class="fixed inset-0 bg-black bg-opacity-90 hidden items-center justify-center z-50" onclick="closeVideoPlayer()">
    <div class="relative w-full max-w-4xl mx-4" onclick="event.stopPropagation()">
        <button onclick="closeVideoPlayer()" class="absolute -top-10 right-0 text-white hover:text-gray-300 text-xl bg-black bg-opacity-50 rounded-full w-8 h-8 flex items-center justify-center">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="bg-black rounded-lg overflow-hidden shadow-2xl">
            <div class="relative" style="padding-bottom: 56.25%;">
                <video id="videoPlayer" controls class="absolute inset-0 w-full h-full" controlsList="nodownload">
                    <source id="videoSource" src="" type="video/mp4">
                    Seu navegador não suporta a reprodução de vídeos.
                </video>
            </div>
            <div class="p-3 bg-gray-900 text-white">
                <h3 id="videoPlayerTitle" class="text-base font-semibold truncate"></h3>
            </div>
        </div>
    </div>
</div>

<script>
function openVideoPlayer(videoUrl, videoTitle) {
    const modal = document.getElementById('videoPlayerModal');
    const player = document.getElementById('videoPlayer');
    const source = document.getElementById('videoSource');
    const title = document.getElementById('videoPlayerTitle');
    
    source.src = videoUrl;
    title.textContent = videoTitle;
    player.load();
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    player.play();
}

function closeVideoPlayer() {
    const modal = document.getElementById('videoPlayerModal');
    const player = document.getElementById('videoPlayer');
    
    player.pause();
    player.currentTime = 0;
    
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
