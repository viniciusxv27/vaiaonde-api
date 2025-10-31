@extends('layouts.influencer')

@section('title', 'Propostas')
@section('page-title', 'Propostas de Parceria')
@section('page-subtitle', 'Gerencie suas propostas de colaboração')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Pendentes</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $pendingCount ?? 0 }}</h3>
            </div>
            <i class="fas fa-clock text-yellow-500 text-2xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Aceitas</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $acceptedCount ?? 0 }}</h3>
            </div>
            <i class="fas fa-check-circle text-green-500 text-2xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Rejeitadas</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $rejectedCount ?? 0 }}</h3>
            </div>
            <i class="fas fa-times-circle text-red-500 text-2xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Ganho</p>
                <h3 class="text-2xl font-bold text-gray-800">R$ {{ number_format($totalEarned ?? 0, 2, ',', '.') }}</h3>
            </div>
            <i class="fas fa-dollar-sign text-blue-500 text-2xl"></i>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4 overflow-x-auto">
            <button class="px-4 py-2 rounded-lg font-medium transition whitespace-nowrap filter-btn {{ request('status', 'all') == 'all' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}" 
                onclick="filterProposals('all')">
                Todas
            </button>
            <button class="px-4 py-2 rounded-lg font-medium transition whitespace-nowrap filter-btn {{ request('status') == 'pending' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}" 
                onclick="filterProposals('pending')">
                Pendentes
            </button>
            <button class="px-4 py-2 rounded-lg font-medium transition whitespace-nowrap filter-btn {{ request('status') == 'accepted' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}" 
                onclick="filterProposals('accepted')">
                Aceitas
            </button>
            <button class="px-4 py-2 rounded-lg font-medium transition whitespace-nowrap filter-btn {{ request('status') == 'submitted_for_approval' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}" 
                onclick="filterProposals('submitted_for_approval')">
                Em Aprovação
            </button>
            <button class="px-4 py-2 rounded-lg font-medium transition whitespace-nowrap filter-btn {{ request('status') == 'completed' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}" 
                onclick="filterProposals('completed')">
                Concluídas
            </button>
            <button class="px-4 py-2 rounded-lg font-medium transition whitespace-nowrap filter-btn {{ request('status') == 'rejected' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}" 
                onclick="filterProposals('rejected')">
                Rejeitadas
            </button>
        </div>
        
        <div class="flex items-center space-x-2">
            <input type="text" placeholder="Buscar propostas..." 
                class="border rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
</div>

<!-- Proposals List -->
<div class="space-y-4">
    @forelse($proposals ?? [] as $proposal)
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                        <!-- Place Photo -->
                        <div class="w-16 h-16 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                            @if($proposal->place && $proposal->place->photo)
                            <img src="{{ asset('storage/' . $proposal->place->photo) }}" alt="{{ $proposal->place->name }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-gray-400 text-xl"></i>
                            </div>
                            @endif
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                <h3 class="text-lg font-semibold text-gray-800">{{ $proposal->place->name ?? 'Estabelecimento' }}</h3>
                                @if($proposal->status == 'pending')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full font-medium">
                                    <i class="fas fa-clock mr-1"></i>Pendente
                                </span>
                                @elseif($proposal->status == 'accepted')
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>Aceita
                                </span>
                                @elseif($proposal->status == 'submitted_for_approval')
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">
                                    <i class="fas fa-hourglass-half mr-1"></i>Aguardando Aprovação
                                </span>
                                @elseif($proposal->status == 'completed')
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs rounded-full font-medium">
                                    <i class="fas fa-check-double mr-1"></i>Concluída
                                </span>
                                @else
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs rounded-full font-medium">
                                    <i class="fas fa-times-circle mr-1"></i>Rejeitada
                                </span>
                                @endif
                            </div>
                            
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $proposal->place->city->name ?? 'N/A' }}</span>
                                <span><i class="fas fa-tag mr-1"></i>{{ $proposal->place->first_category->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Message -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-comment-alt mr-2 text-purple-600"></i>Descrição da Proposta:
                        </p>
                        <p class="text-sm text-gray-600">{{ $proposal->description }}</p>
                    </div>
                    
                    <!-- Details -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="flex items-center space-x-2">
                            <div class="bg-green-100 rounded-lg p-2">
                                <i class="fas fa-dollar-sign text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Valor Oferecido</p>
                                <p class="font-semibold text-green-600">R$ {{ number_format($proposal->amount, 2, ',', '.') }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <div class="bg-blue-100 rounded-lg p-2">
                                <i class="fas fa-calendar text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Prazo de Entrega</p>
                                <p class="font-semibold text-gray-800">{{ $proposal->deadline_days }} dias</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <div class="bg-purple-100 rounded-lg p-2">
                                <i class="fas fa-user text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Proprietário</p>
                                <p class="font-semibold text-gray-800">{{ $proposal->place->owner->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <div class="bg-orange-100 rounded-lg p-2">
                                <i class="fas fa-clock text-orange-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Recebida em</p>
                                <p class="font-semibold text-gray-800">{{ $proposal->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="mt-6 pt-6 border-t flex items-center justify-between">
                <div class="flex items-center space-x-3 text-sm">
                    @if($proposal->status == 'accepted')
                    @php
                        $chat = \App\Models\Chat::where('influencer_id', auth()->id())
                            ->where('place_id', $proposal->place_id)
                            ->first();
                    @endphp
                    @if($chat)
                    <a href="{{ route('influencer.chats.show', $chat->id) }}" class="text-purple-600 hover:text-purple-700 font-medium">
                        <i class="fas fa-comments mr-1"></i>Conversar com Proprietário
                    </a>
                    @endif
                    @endif
                </div>
                
                <div class="flex space-x-2">
                    @if($proposal->status == 'pending')
                    <form method="POST" action="{{ route('influencer.proposals.reject', $proposal->id) }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-times mr-2"></i>Rejeitar
                        </button>
                    </form>
                    
                    <form method="POST" action="{{ route('influencer.proposals.delete', $proposal->id) }}" onsubmit="return confirm('Tem certeza que deseja excluir esta proposta? Esta ação não pode ser desfeita.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-trash mr-2"></i>Excluir
                        </button>
                    </form>
                    @elseif($proposal->status == 'accepted')
                    <a href="{{ route('influencer.videos.index') }}?proposal_id={{ $proposal->id }}" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition inline-block">
                        <i class="fas fa-video mr-2"></i>Enviar Vídeo
                    </a>
                    <form method="POST" action="{{ route('influencer.proposals.submit', $proposal->id) }}">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-check-double mr-2"></i>Enviar para Aprovação
                        </button>
                    </form>
                    @elseif($proposal->status == 'submitted_for_approval')
                    <span class="text-sm text-blue-600 flex items-center">
                        <i class="fas fa-hourglass-half mr-2"></i>Aguardando aprovação do proprietário
                    </span>
                    @elseif($proposal->status == 'completed')
                    <span class="text-sm text-green-600 flex items-center">
                        <i class="fas fa-check-double mr-2"></i>Proposta concluída - Pagamento recebido!
                    </span>
                    @else
                    <span class="text-sm text-gray-500">Proposta {{ $proposal->status == 'rejected' ? 'rejeitada' : 'cancelada' }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhuma proposta encontrada</h3>
        <p class="text-gray-500 mb-6">Você ainda não recebeu propostas de parceria.</p>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 max-w-md mx-auto">
            <p class="text-sm text-blue-800">
                <i class="fas fa-lightbulb mr-2"></i>
                <strong>Dica:</strong> Mantenha seu perfil atualizado e publique conteúdo de qualidade para atrair mais parceiros!
            </p>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if(isset($proposals) && $proposals->hasPages())
<div class="mt-6">
    {{ $proposals->links() }}
</div>
@endif
@endsection

@push('scripts')
<script>
function filterProposals(status) {
    const url = new URL(window.location.href);
    if (status === 'all') {
        url.searchParams.delete('status');
    } else {
        url.searchParams.set('status', status);
    }
    window.location.href = url.toString();
}
</script>
@endpush
