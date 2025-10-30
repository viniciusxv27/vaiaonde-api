@extends('layouts.influencer')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Visão geral da sua performance')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total de Propostas -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total de Propostas</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $totalProposals ?? 0 }}</h3>
            </div>
            <div class="bg-purple-100 rounded-full p-4">
                <i class="fas fa-handshake text-purple-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm">
            <span class="text-green-600">
                <i class="fas fa-arrow-up"></i> {{ $acceptedProposals ?? 0 }} aceitas
            </span>
        </div>
    </div>

    <!-- Vídeos Publicados -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Vídeos Publicados</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $totalVideos ?? 0 }}</h3>
            </div>
            <div class="bg-blue-100 rounded-full p-4">
                <i class="fas fa-video text-blue-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm">
            <span class="text-gray-600">
                <i class="fas fa-eye"></i> {{ number_format($totalViews ?? 0) }} visualizações
            </span>
        </div>
    </div>

    <!-- Ganhos do Mês -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Ganhos do Mês</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">R$ {{ number_format($monthEarnings ?? 0, 2, ',', '.') }}</h3>
            </div>
            <div class="bg-green-100 rounded-full p-4">
                <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm">
            <span class="text-green-600">
                <i class="fas fa-arrow-up"></i> {{ $earningsGrowth ?? 0 }}% vs mês passado
            </span>
        </div>
    </div>

    <!-- Saldo Disponível -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Saldo Disponível</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">R$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2, ',', '.') }}</h3>
            </div>
            <div class="bg-yellow-100 rounded-full p-4">
                <i class="fas fa-wallet text-yellow-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('influencer.wallet') }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                <i class="fas fa-arrow-right"></i> Sacar agora
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Proposals -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">
                    <i class="fas fa-handshake text-purple-600 mr-2"></i>Propostas Recentes
                </h3>
                <a href="{{ route('influencer.proposals') }}" class="text-sm text-purple-600 hover:text-purple-700">
                    Ver todas <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="divide-y">
                @forelse($recentProposals ?? [] as $proposal)
                <div class="p-6 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <h4 class="font-semibold text-gray-800">{{ $proposal->place_name }}</h4>
                                @if($proposal->status == 'pending')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                                    Pendente
                                </span>
                                @elseif($proposal->status == 'accepted')
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                    Aceita
                                </span>
                                @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                    Rejeitada
                                </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($proposal->message, 120) }}</p>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $proposal->city_name }}</span>
                                <span><i class="fas fa-dollar-sign mr-1"></i>R$ {{ number_format($proposal->payment_amount, 2, ',', '.') }}</span>
                                <span><i class="fas fa-calendar mr-1"></i>{{ $proposal->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @if($proposal->status == 'pending')
                        <div class="flex space-x-2 ml-4">
                            <form method="POST" action="{{ route('influencer.proposals.accept', $proposal->id) }}">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition">
                                    <i class="fas fa-check"></i> Aceitar
                                </button>
                            </form>
                            <form method="POST" action="{{ route('influencer.proposals.reject', $proposal->id) }}">
                                @csrf
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
                                    <i class="fas fa-times"></i> Rejeitar
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>Nenhuma proposta recente</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Performance Chart & Quick Actions -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>Ações Rápidas
            </h3>
            <div class="space-y-3">
                <a href="{{ route('influencer.videos.index') }}" class="block bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg hover:from-purple-600 hover:to-purple-700 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold">Enviar Vídeo</p>
                            <p class="text-xs text-purple-100">Publique seu conteúdo</p>
                        </div>
                        <i class="fas fa-upload text-2xl"></i>
                    </div>
                </a>
                
                <a href="{{ route('influencer.proposals') }}" class="block bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold">Ver Propostas</p>
                            <p class="text-xs text-blue-100">{{ $pendingProposals ?? 0 }} pendentes</p>
                        </div>
                        <i class="fas fa-handshake text-2xl"></i>
                    </div>
                </a>
                
                <a href="{{ route('influencer.wallet') }}" class="block bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg hover:from-green-600 hover:to-green-700 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold">Sacar Saldo</p>
                            <p class="text-xs text-green-100">R$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2, ',', '.') }}</p>
                        </div>
                        <i class="fas fa-money-bill-wave text-2xl"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Videos -->
<div class="mt-6 bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold">
            <i class="fas fa-video text-blue-600 mr-2"></i>Vídeos Recentes
        </h3>
        <a href="{{ route('influencer.videos.index') }}" class="text-sm text-purple-600 hover:text-purple-700">
            Ver todos <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 p-6">
        @forelse($recentVideos ?? [] as $video)
        <div class="group cursor-pointer" onclick="openVideoPlayer('{{ $video->video_url }}', '{{ addslashes($video->title) }}')">
            <div class="relative overflow-hidden rounded-lg mb-3 aspect-video bg-gray-200">
                @if($video->thumbnail_url)
                <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-video text-gray-400 text-4xl"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition flex items-center justify-center">
                    <i class="fas fa-play-circle text-white text-4xl opacity-0 group-hover:opacity-100 transition"></i>
                </div>
            </div>
            <h4 class="font-semibold text-sm mb-1 truncate">{{ $video->title }}</h4>
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span><i class="fas fa-eye mr-1"></i>{{ number_format($video->views_count ?? 0) }}</span>
                <span>{{ $video->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-4 p-12 text-center text-gray-500">
            <i class="fas fa-video-slash text-4xl mb-3"></i>
            <p>Nenhum vídeo publicado ainda</p>
            <a href="{{ route('influencer.videos.index') }}" class="inline-block mt-4 bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-upload mr-2"></i>Enviar Primeiro Vídeo
            </a>
        </div>
        @endforelse
    </div>
</div>

<!-- Video Player Modal -->
<div id="videoPlayerModal" class="fixed inset-0 bg-black bg-opacity-90 hidden items-center justify-center z-50" onclick="closeVideoPlayer()">
    <div class="relative w-full max-w-4xl mx-4" onclick="event.stopPropagation()">
        <button onclick="closeVideoPlayer()" class="absolute -top-10 right-0 text-white hover:text-gray-300 text-xl bg-black bg-opacity-50 rounded-full w-8 h-8 flex items-center justify-center">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="bg-black rounded-lg overflow-hidden shadow-2xl">
            <div class="relative" style="padding-bottom: 56.25%; /* 16:9 aspect ratio */">
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

@endsection

@push('scripts')
<script>
// Video Player Functions
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
    
    // Auto play
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
@endpush
