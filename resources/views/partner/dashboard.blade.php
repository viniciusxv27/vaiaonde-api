SQLSTATE[HY000]: General error: 1364 Field 'balance_before' doesn't have a default value
@extends('layouts.partner')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard do Parceiro')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 text-white">
        <h2 class="text-2xl font-bold">Bem-vindo, {{ auth()->user()->name }}! 👋</h2>
        <p class="mt-2">Confira as métricas dos seus estabelecimentos</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Meus Lugares</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['total_places'] ?? 0 }}</h3>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-store text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total de Menções</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['total_mentions'] ?? 0 }}</h3>
                </div>
                <div class="bg-purple-100 rounded-full p-4">
                    <i class="fas fa-video text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Contratos Ativos</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['active_contracts'] ?? 0 }}</h3>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-handshake text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Visualizações Totais</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_views'] ?? 0) }}</h3>
                </div>
                <div class="bg-yellow-100 rounded-full p-4">
                    <i class="fas fa-eye text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Views Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Visualizações nos Últimos 7 Dias</h3>
            <canvas id="viewsChart"></canvas>
        </div>
        
        <!-- Places Performance -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Performance por Lugar</h3>
            <canvas id="placesChart"></canvas>
        </div>
    </div>

    <!-- Recent Videos and Proposals -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Mentions -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold">Vídeos Recentes (Menções)</h3>
                <a href="{{ route('partner.videos') }}" class="text-blue-600 hover:text-blue-700 text-sm">Ver todos</a>
            </div>
            <div class="divide-y max-h-96 overflow-y-auto">
                @forelse($recentVideos ?? [] as $video)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center space-x-4">
                        <img src="{{ $video->thumbnail_url }}" class="w-20 h-20 rounded object-cover">
                        <div class="flex-1">
                            <h4 class="font-medium">{{ $video->title }}</h4>
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-user-circle"></i> {{ $video->user->name }}
                            </p>
                            <p class="text-xs text-gray-400">
                                <i class="fas fa-map-marker-alt"></i> {{ $video->place->name }}
                            </p>
                            <div class="flex items-center space-x-3 text-xs text-gray-400 mt-2">
                                <span><i class="fas fa-eye"></i> {{ number_format($video->views_count ?? 0) }}</span>
                                <span><i class="fas fa-heart"></i> {{ number_format($video->likes_count ?? 0) }}</span>
                                <span><i class="fas fa-share"></i> {{ number_format($video->shares_count ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-video text-4xl mb-2"></i>
                    <p>Nenhum vídeo mencionando seus lugares ainda</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Active Proposals -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold">Contratos Ativos</h3>
                <a href="{{ route('partner.proposals') }}" class="text-blue-600 hover:text-blue-700 text-sm">Ver todos</a>
            </div>
            <div class="divide-y max-h-96 overflow-y-auto">
                @forelse($activeProposals ?? [] as $proposal)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h4 class="font-medium">{{ $proposal->title }}</h4>
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-user-circle"></i> {{ $proposal->influencer->name }}
                            </p>
                            <p class="text-xs text-gray-400">
                                <i class="fas fa-map-marker-alt"></i> {{ $proposal->place->name }}
                            </p>
                            <div class="mt-2">
                                @if($proposal->status == 'pending')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">
                                    <i class="fas fa-clock"></i> Aguardando
                                </span>
                                @elseif($proposal->status == 'accepted')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                    <i class="fas fa-check"></i> Em Andamento
                                </span>
                                @elseif($proposal->status == 'completed')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">
                                    <i class="fas fa-check-double"></i> Concluído
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right ml-4">
                            <p class="font-bold text-green-600">R$ {{ number_format($proposal->amount, 2, ',', '.') }}</p>
                            <p class="text-xs text-gray-400">{{ $proposal->deadline_days }} dias</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-handshake text-4xl mb-2"></i>
                    <p>Nenhum contrato ativo no momento</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('partner.featured') }}" class="bg-gradient-to-r from-yellow-400 to-yellow-600 text-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center space-x-4">
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-star text-3xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Destaque no App</h3>
                    <p class="text-sm opacity-90">R$ 39,90 por 30 dias</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('partner.wallet') }}" class="bg-gradient-to-r from-green-400 to-green-600 text-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center space-x-4">
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-wallet text-3xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Adicionar Saldo</h3>
                    <p class="text-sm opacity-90">Recarregue sua carteira</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('partner.places') }}" class="bg-gradient-to-r from-blue-400 to-blue-600 text-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center space-x-4">
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-store text-3xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Meus Lugares</h3>
                    <p class="text-sm opacity-90">Gerencie seus locais</p>
                </div>
            </div>
        </a>
    </div>
</div>

<script>
// Views Chart
const viewsCtx = document.getElementById('viewsChart').getContext('2d');
new Chart(viewsCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData['dates'] ?? ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom']) !!},
        datasets: [{
            label: 'Visualizações',
            data: {!! json_encode($chartData['views'] ?? [120, 190, 300, 250, 200, 300, 400]) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Places Chart
const placesCtx = document.getElementById('placesChart').getContext('2d');
new Chart(placesCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($placeNames ?? ['Lugar 1', 'Lugar 2', 'Lugar 3']) !!},
        datasets: [{
            data: {!! json_encode($placeViews ?? [300, 200, 150]) !!},
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endsection
