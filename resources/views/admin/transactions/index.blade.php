@extends('layouts.admin')

@section('title', 'Transações')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Transações</h1>
    <p class="text-gray-600 mt-1">Histórico de todas as transações financeiras</p>
</div>

<!-- Filtro rápido para saques pendentes -->
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-yellow-800">Saques Pendentes de Aprovação</h3>
            <p class="text-sm text-yellow-700 mt-1">
                {{ \App\Models\Transaction::where('type', 'withdrawal')->where('status', 'pending')->count() }} saque(s) aguardando aprovação
            </p>
        </div>
        <a href="?type=withdrawal&status=pending" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
            Ver Pendentes
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($transactions as $transaction)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->user->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full
                        {{ $transaction->type == 'deposit' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $transaction->type == 'withdrawal' ? 'bg-red-100 text-red-800' : '' }}
                        {{ in_array($transaction->type, ['transfer_out', 'featured_payment', 'proposal_payment']) ? 'bg-purple-100 text-purple-800' : '' }}">
                        @if($transaction->type == 'deposit') Depósito
                        @elseif($transaction->type == 'withdrawal') Saque
                        @elseif($transaction->type == 'transfer_out') Transferência
                        @elseif($transaction->type == 'featured_payment') Destaque
                        @elseif($transaction->type == 'proposal_payment') Proposta
                        @else {{ $transaction->type }}
                        @endif
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full
                        {{ $transaction->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $transaction->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $transaction->status == 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                        @if($transaction->status == 'pending') Pendente
                        @elseif($transaction->status == 'completed') Completo
                        @elseif($transaction->status == 'failed') Falhou
                        @else {{ $transaction->status }}
                        @endif
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    @if($transaction->type == 'withdrawal' && $transaction->status == 'pending')
                    <form action="{{ route('admin.transactions.approve', $transaction->id) }}" method="POST" class="inline mr-2">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-800" title="Aprovar">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.transactions.reject', $transaction->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza?')">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800" title="Rejeitar">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                    @else
                    <span class="text-gray-400">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    Nenhuma transação encontrada
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $transactions->links() }}
</div>
@endsection
