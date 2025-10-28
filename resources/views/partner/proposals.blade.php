@extends('layouts.partner')

@section('title', 'Contratos')
@section('page-title', 'Contratos com Influenciadores')

@section('content')
<div class="space-y-6">
    <!-- Filter Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <a href="?status=all" class="py-4 px-1 border-b-2 font-medium text-sm {{ request('status', 'all') == 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Todas ({{ $counts['total'] ?? 0 }})
                </a>
                <a href="?status=pending" class="py-4 px-1 border-b-2 font-medium text-sm {{ request('status') == 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Pendentes ({{ $counts['pending'] ?? 0 }})
                </a>
                <a href="?status=accepted" class="py-4 px-1 border-b-2 font-medium text-sm {{ request('status') == 'accepted' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Aceitas ({{ $counts['accepted'] ?? 0 }})
                </a>
                <a href="?status=completed" class="py-4 px-1 border-b-2 font-medium text-sm {{ request('status') == 'completed' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Concluídas ({{ $counts['completed'] ?? 0 }})
                </a>
                <a href="?status=rejected" class="py-4 px-1 border-b-2 font-medium text-sm {{ request('status') == 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Rejeitadas ({{ $counts['rejected'] ?? 0 }})
                </a>
            </nav>
        </div>
    </div>

    <!-- Proposals List -->
    <div class="space-y-4">
        @forelse($proposals ?? [] as $proposal)
        <div class="bg-white rounded-lg shadow hover:shadow-md transition">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <!-- Proposal Info -->
                    <div class="flex-1">
                        <div class="flex items-start space-x-4">
                            <!-- Influencer Avatar -->
                            <img src="https://ui-avatars.com/api/?name={{ $proposal->influencer->name }}&background=3B82F6&color=fff" class="w-16 h-16 rounded-full">
                            
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-lg font-semibold">{{ $proposal->title }}</h3>
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
                                        <i class="fas fa-check-double"></i> Concluída
                                    </span>
                                    @elseif($proposal->status == 'rejected')
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">
                                        <i class="fas fa-times"></i> Rejeitada
                                    </span>
                                    @endif
                                </div>
                                
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-user-circle"></i> Influenciador: <span class="font-medium">{{ $proposal->influencer->name }}</span>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-map-marker-alt"></i> Lugar: <span class="font-medium">{{ $proposal->place->name }}</span>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-calendar"></i> Prazo: <span class="font-medium">{{ $proposal->deadline_days }} dias</span>
                                </p>
                                
                                <div class="mt-3">
                                    <p class="text-sm text-gray-700">{{ $proposal->description }}</p>
                                </div>
                                
                                <div class="mt-3 text-xs text-gray-500">
                                    Enviada em {{ $proposal->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Amount & Actions -->
                    <div class="ml-6 text-right">
                        <p class="text-3xl font-bold text-green-600">R$ {{ number_format($proposal->amount, 2, ',', '.') }}</p>
                        
                        @if($proposal->status == 'pending')
                        <div class="mt-4 space-y-2">
                            <form method="POST" action="{{ route('partner.proposals.accept', $proposal->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition">
                                    <i class="fas fa-check mr-2"></i>Aceitar
                                </button>
                            </form>
                            <form method="POST" action="{{ route('partner.proposals.reject', $proposal->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700 transition">
                                    <i class="fas fa-times mr-2"></i>Rejeitar
                                </button>
                            </form>
                        </div>
                        @elseif($proposal->status == 'accepted')
                        <div class="mt-4">
                            <button class="bg-blue-100 text-blue-800 px-6 py-2 rounded-lg font-semibold">
                                <i class="fas fa-hourglass-half mr-2"></i>Aguardando Conclusão
                            </button>
                        </div>
                        @elseif($proposal->status == 'completed')
                        <div class="mt-4">
                            <p class="text-xs text-gray-500">Concluída em</p>
                            <p class="text-sm font-medium">{{ $proposal->completed_at?->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                        
                        <div class="mt-4">
                            <a href="{{ route('partner.chat', ['proposal_id' => $proposal->id]) }}" class="text-blue-600 hover:text-blue-700 text-sm">
                                <i class="fas fa-comments mr-1"></i>Chat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
            <i class="fas fa-handshake text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Nenhum contrato encontrado</h3>
            <p>Você ainda não recebeu propostas de influenciadores</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(isset($proposals) && $proposals->hasPages())
    <div class="bg-white rounded-lg shadow p-4">
        {{ $proposals->links() }}
    </div>
    @endif
</div>
@endsection
