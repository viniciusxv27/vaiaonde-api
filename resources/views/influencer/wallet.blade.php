@extends('layouts.influencer')

@section('title', 'Carteira')
@section('page-title', 'Minha Carteira')
@section('page-subtitle', 'Gerencie seus ganhos e saques')

@section('content')
<!-- Balance Card -->
<div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-lg shadow-lg p-8 text-white mb-8">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-purple-200 mb-2">Saldo Disponível</p>
            <h2 class="text-4xl font-bold mb-4">R$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2, ',', '.') }}</h2>
            <div class="flex space-x-4">
                <button onclick="openDepositModal()" class="bg-green-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-600 transition">
                    <i class="fas fa-plus mr-2"></i>Depositar
                </button>
                <button onclick="openWithdrawModal()" class="bg-white text-purple-600 px-6 py-2 rounded-lg font-semibold hover:bg-purple-50 transition">
                    <i class="fas fa-money-bill-wave mr-2"></i>Sacar
                </button>
            </div>
        </div>
        <div class="hidden md:block">
            <div class="bg-purple-700 bg-opacity-50 rounded-full p-6">
                <i class="fas fa-wallet text-6xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Total Recebido</p>
                <h3 class="text-2xl font-bold text-gray-800">R$ {{ number_format($totalReceived ?? 0, 2, ',', '.') }}</h3>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-arrow-down text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Total Sacado</p>
                <h3 class="text-2xl font-bold text-gray-800">R$ {{ number_format($totalWithdrawn ?? 0, 2, ',', '.') }}</h3>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <i class="fas fa-arrow-up text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Ganhos Este Mês</p>
                <h3 class="text-2xl font-bold text-gray-800">R$ {{ number_format($monthEarnings ?? 0, 2, ',', '.') }}</h3>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Transactions History -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold">
            <i class="fas fa-history text-purple-600 mr-2"></i>Histórico de Transações
        </h3>
        
        <!-- Filters -->
        <div class="flex space-x-2">
            <select class="border rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">Todos os tipos</option>
                <option value="earning">Ganhos</option>
                <option value="withdrawal">Saques</option>
            </select>
            <select class="border rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">Último mês</option>
                <option value="7">Últimos 7 dias</option>
                <option value="30">Últimos 30 dias</option>
                <option value="90">Últimos 90 dias</option>
                <option value="all">Todos</option>
            </select>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($transactions ?? [] as $transaction)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($transaction->type == 'earning')
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                            <i class="fas fa-arrow-down mr-1"></i>Ganho
                        </span>
                        @elseif($transaction->type == 'withdrawal')
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                            <i class="fas fa-arrow-up mr-1"></i>Saque
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $transaction->description }}
                    </td>
                    <td class="px-6 py-4">
                        @if($transaction->status == 'completed')
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">
                            <i class="fas fa-check-circle"></i> Concluído
                        </span>
                        @elseif($transaction->status == 'pending')
                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">
                            <i class="fas fa-clock"></i> Pendente
                        </span>
                        @else
                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">
                            <i class="fas fa-times-circle"></i> Falhou
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right font-semibold {{ $transaction->type == 'earning' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $transaction->type == 'earning' ? '+' : '-' }}R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Nenhuma transação encontrada</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(isset($transactions) && $transactions->hasPages())
    <div class="p-6 border-t">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection

<!-- Deposit Modal -->
<div id="depositModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4" x-data="{ method: 'pix' }">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-xl font-semibold">Adicionar Saldo</h3>
            <button onclick="closeDepositModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <!-- Seleção de Método -->
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Método de Pagamento</label>
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" @click="method = 'pix'" :class="method === 'pix' ? 'border-[#FEB800] bg-[#FEB800] bg-opacity-10' : 'border-gray-300'" class="border-2 rounded-lg p-4 text-center hover:border-[#FEB800] transition">
                        <i class="fas fa-qrcode text-2xl mb-2" :class="method === 'pix' ? 'text-[#FEB800]' : 'text-gray-600'"></i>
                        <p class="font-medium">PIX</p>
                        <p class="text-xs text-gray-500">Instantâneo</p>
                    </button>
                    <button type="button" @click="method = 'card'" :class="method === 'card' ? 'border-black bg-black bg-opacity-5' : 'border-gray-300'" class="border-2 rounded-lg p-4 text-center hover:border-black transition">
                        <i class="fas fa-credit-card text-2xl mb-2" :class="method === 'card' ? 'text-black' : 'text-gray-600'"></i>
                        <p class="font-medium">Cartão</p>
                        <p class="text-xs text-gray-500">Crédito/Débito</p>
                    </button>
                </div>
            </div>

            <!-- Formulário PIX -->
            <form x-show="method === 'pix'" method="POST" action="{{ route('influencer.wallet.deposit') }}">
                @csrf
                <input type="hidden" name="payment_method" value="pix">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Valor do Depósito</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">R$</span>
                        <input type="number" name="amount" step="0.01" min="10" 
                            class="w-full border rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-[#FEB800] focus:border-transparent" 
                            placeholder="0,00" required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Valor mínimo: R$ 10,00</p>
                </div>
                
                <div class="bg-[#FEB800] bg-opacity-10 border border-[#FEB800] rounded-lg p-3 mb-4">
                    <p class="text-sm text-black">
                        <i class="fas fa-info-circle mr-1 text-[#FEB800]"></i>
                        <strong>Como funciona:</strong><br>
                        • Gere um QR Code PIX<br>
                        • Escaneie com o app do seu banco<br>
                        • O saldo é creditado instantaneamente
                    </p>
                </div>
                
                <button type="submit" class="w-full bg-[#FEB800] text-black py-3 rounded-lg font-bold hover:bg-black hover:text-white transition">
                <button type="submit" class="w-full bg-[#FEB800] text-black py-3 rounded-lg font-bold hover:bg-black hover:text-white transition">
                    <i class="fas fa-qrcode mr-2"></i>Gerar QR Code PIX
                </button>
            </form>

            <!-- Formulário Cartão -->
            <form x-show="method === 'card'" id="depositForm" method="POST" action="{{ route('influencer.wallet.deposit') }}">
                @csrf
                <input type="hidden" name="payment_method" value="card">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Valor do Depósito</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">R$</span>
                        <input type="number" id="depositAmount" name="amount" step="0.01" min="10" 
                            class="w-full border rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent" 
                            placeholder="0,00" required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Valor mínimo: R$ 10,00</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Informações do Cartão</label>
                    <div id="card-element" class="border rounded-lg p-3 bg-gray-50"></div>
                    <div id="card-errors" class="text-red-500 text-xs mt-2"></div>
                </div>
                
                <button type="submit" id="submitBtn" class="w-full bg-black text-white py-3 rounded-lg font-bold hover:bg-[#FEB800] hover:text-black transition border-2 border-black">
                    <i class="fas fa-credit-card mr-2"></i>Pagar com Cartão
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Withdraw Modal -->
<div id="withdrawModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-xl font-semibold">Sacar Saldo</h3>
            <button onclick="closeWithdrawModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            @if(!auth()->user()->cpf || !auth()->user()->pix_key)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                    <div>
                        <p class="text-sm text-yellow-800 font-medium">Dados incompletos</p>
                        <p class="text-xs text-yellow-700 mt-1">
                            Configure seu CPF e chave PIX no 
                            <a href="{{ route('influencer.profile') }}" class="underline font-medium">seu perfil</a> 
                            para realizar saques.
                        </p>
                    </div>
                </div>
            </div>
            @endif
            
            <form method="POST" action="{{ route('influencer.wallet.withdraw') }}">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Valor do Saque</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">R$</span>
                        <input type="number" name="amount" step="0.01" min="10" max="{{ auth()->user()->wallet_balance ?? 0 }}" 
                            class="w-full border rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                            placeholder="0,00" required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Valor mínimo: R$ 10,00</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Chave PIX</label>
                    <input type="text" name="pix_key" value="{{ auth()->user()->pix_key }}" 
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-gray-50" readonly>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Informações importantes:</strong><br>
                        • O saque será processado em até 24 horas úteis<br>
                        • Taxa de processamento: R$ 0,00<br>
                        • Valor mínimo para saque: R$ 10,00
                    </p>
                </div>
                
                <button type="submit" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition" 
                    {{ !auth()->user()->cpf || !auth()->user()->pix_key ? 'disabled' : '' }}>
                    <i class="fas fa-check mr-2"></i>Confirmar Saque
                </button>
            </form>
        </div>
    </div>
</div>

<!-- PIX QR Code Modal -->
@if(session('show_pix_modal'))
<div id="pixModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-xl font-semibold">
                <i class="fas fa-qrcode text-green-600 mr-2"></i>Pagamento PIX
            </h3>
            <button onclick="closePixModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6 text-center">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <i class="fas fa-check-circle text-green-600 text-3xl mb-2"></i>
                <p class="text-green-800 font-medium">QR Code PIX gerado com sucesso!</p>
                <p class="text-green-600 text-sm mt-1">Válido por 1 hora</p>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <div class="flex items-center justify-center">
                    <i class="fas fa-sync fa-spin text-blue-600 mr-2"></i>
                    <p class="text-sm text-blue-800">Aguardando pagamento...</p>
                </div>
            </div>
            
            @if(session('pix_qr_code_url'))
            <div class="bg-white border-2 border-gray-200 rounded-lg p-4 mb-4">
                <img src="{{ session('pix_qr_code_url') }}" alt="QR Code PIX" class="mx-auto" style="width: 250px; height: 250px;">
            </div>
            @endif
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Código PIX Copia e Cola</label>
                <div class="flex gap-2">
                    <input type="text" id="pixCode" value="{{ session('pix_qr_code') }}" 
                        class="flex-1 border rounded-lg px-3 py-2 text-sm bg-gray-50 font-mono" readonly>
                    <button onclick="copyPixCode()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Como pagar:</strong><br>
                    1. Abra o app do seu banco<br>
                    2. Escolha pagar com PIX<br>
                    3. Escaneie o QR Code ou cole o código acima
                </p>
            </div>
            
            <p class="text-xs text-gray-500">
                <i class="fas fa-clock mr-1"></i>
                Após o pagamento, o saldo será creditado automaticamente.
            </p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pixId = '{{ session("pix_id") }}';
        const pixAmount = {{ session('pix_amount') ?? 0 }};
        
        if (pixId && pixAmount > 0) {
            startPixPolling(pixId, pixAmount);
        }
    });
</script>
@endif

@push('scripts')
<script>
function openDepositModal() {
    document.getElementById('depositModal').classList.remove('hidden');
    document.getElementById('depositModal').classList.add('flex');
}

function closeDepositModal() {
    document.getElementById('depositModal').classList.add('hidden');
    document.getElementById('depositModal').classList.remove('flex');
}

function openWithdrawModal() {
    document.getElementById('withdrawModal').classList.remove('hidden');
    document.getElementById('withdrawModal').classList.add('flex');
}

function closeWithdrawModal() {
    document.getElementById('withdrawModal').classList.add('hidden');
    document.getElementById('withdrawModal').classList.remove('flex');
}

function closePixModal() {
    const pixModal = document.getElementById('pixModal');
    if (pixModal) {
        pixModal.classList.add('hidden');
        pixModal.classList.remove('flex');
        stopPixPolling();
    }
}

function copyPixCode() {
    const pixCode = document.getElementById('pixCode');
    pixCode.select();
    document.execCommand('copy');
    
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
    }, 2000);
}

// PIX Polling
const PIX_POLLING_INTERVAL = 3000; // 3 segundos
const PIX_POLLING_TIMEOUT = 300000; // 5 minutos
let pixPollingInterval = null;

function startPixPolling(pixId, amount) {
    if (!pixId || !amount) {
        console.error('PIX ID ou amount não fornecido');
        return;
    }
    
    let elapsedTime = 0;
    
    pixPollingInterval = setInterval(async () => {
        elapsedTime += PIX_POLLING_INTERVAL;
        
        if (elapsedTime >= PIX_POLLING_TIMEOUT) {
            stopPixPolling();
            showPixExpiredMessage();
            return;
        }
        
        try {
            const response = await fetch('{{ route("influencer.wallet.check-pix") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    pix_id: pixId,
                    amount: amount
                })
            });
            
            const data = await response.json();
            
            if (data.success && data.paid) {
                stopPixPolling();
                showPixSuccessMessage();
            }
        } catch (error) {
            console.error('Erro ao verificar pagamento PIX:', error);
        }
    }, PIX_POLLING_INTERVAL);
}

function stopPixPolling() {
    if (pixPollingInterval) {
        clearInterval(pixPollingInterval);
        pixPollingInterval = null;
    }
}

function showPixSuccessMessage() {
    closePixModal();
    
    const successDiv = document.createElement('div');
    successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50';
    successDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-check-circle text-2xl mr-3"></i>
            <div>
                <p class="font-bold">Pagamento Confirmado!</p>
                <p class="text-sm">Seu saldo foi atualizado com sucesso.</p>
            </div>
        </div>
    `;
    document.body.appendChild(successDiv);
    
    setTimeout(() => {
        window.location.reload();
    }, 2000);
}

function showPixExpiredMessage() {
    closePixModal();
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50';
    errorDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-times-circle text-2xl mr-3"></i>
            <div>
                <p class="font-bold">Pagamento Expirado</p>
                <p class="text-sm">O código PIX expirou. Tente novamente.</p>
            </div>
        </div>
    `;
    document.body.appendChild(errorDiv);
    
    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}

// Stripe Configuration
const stripe = Stripe('{{ env("STRIPE_KEY") }}');
const elements = stripe.elements();
const cardElement = elements.create('card', {
    style: {
        base: {
            fontSize: '16px',
            color: '#000000',
            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    }
});

// Mount card element when modal opens
setTimeout(() => {
    if (document.getElementById('card-element')) {
        cardElement.mount('#card-element');
    }
}, 100);

// Handle card errors
cardElement.on('change', function(event) {
    const displayError = document.getElementById('card-errors');
    if (displayError) {
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    }
});

// Handle form submission
const form = document.getElementById('depositForm');
if (form) {
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processando...';
        
        const {token, error} = await stripe.createToken(cardElement);
        
        if (error) {
            const errorElement = document.getElementById('card-errors');
            if (errorElement) {
                errorElement.textContent = error.message;
            }
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-credit-card mr-2"></i>Pagar com Cartão';
        } else {
            // Add token to form
            const hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);
            
            // Submit form
            form.submit();
        }
    });
}
</script>
@endpush
