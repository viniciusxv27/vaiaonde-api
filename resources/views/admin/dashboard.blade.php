@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Administrativo')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total de Usuários</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['total_users'] ?? 0 }}</h3>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total de Vídeos</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['total_videos'] ?? 0 }}</h3>
                </div>
                <div class="bg-purple-100 rounded-full p-4">
                    <i class="fas fa-video text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Propostas Ativas</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $stats['active_proposals'] ?? 0 }}</h3>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-handshake text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Receita Total</p>
                    <h3 class="text-3xl font-bold text-gray-800">R$ {{ number_format($stats['total_revenue'] ?? 0, 2, ',', '.') }}</h3>
                </div>
                <div class="bg-yellow-100 rounded-full p-4">
                    <i class="fas fa-dollar-sign text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Users Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Crescimento de Usuários</h3>
            <canvas id="usersChart"></canvas>
        </div>
        
        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Receita Mensal</h3>
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Videos -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold">Vídeos Recentes</h3>
            </div>
            <div class="divide-y">
                @forelse($recentVideos ?? [] as $video)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center space-x-4">
                        <img src="{{ $video->thumbnail_url }}" class="w-16 h-16 rounded object-cover">
                        <div class="flex-1">
                            <h4 class="font-medium">{{ $video->title }}</h4>
                            <p class="text-sm text-gray-500">{{ $video->user->name }}</p>
                            <div class="flex items-center space-x-3 text-xs text-gray-400 mt-1">
                                <span><i class="fas fa-eye"></i> {{ $video->views }}</span>
                                <span><i class="fas fa-heart"></i> {{ $video->likes }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-gray-500">Nenhum vídeo encontrado</div>
                @endforelse
            </div>
        </div>
        
        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold">Transações Recentes</h3>
            </div>
            <div class="divide-y">
                @forelse($recentTransactions ?? [] as $transaction)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium">{{ $transaction->user->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $transaction->description }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold {{ $transaction->type == 'deposit' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type == 'deposit' ? '+' : '-' }}R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-400">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-gray-500">Nenhuma transação encontrada</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
// Users Chart
const usersCtx = document.getElementById('usersChart').getContext('2d');
new Chart(usersCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(collect(range(11, 0))->map(fn($i) => now()->subMonths($i)->format('M/Y'))) !!},
        datasets: [{
            label: 'Usuários',
            data: {!! json_encode($userGrowth) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(collect(range(11, 0))->map(fn($i) => now()->subMonths($i)->format('M/Y'))) !!},
        datasets: [{
            label: 'Receita',
            data: {!! json_encode($monthlyRevenue) !!},
            backgroundColor: 'rgba(34, 197, 94, 0.8)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>
@endsection
