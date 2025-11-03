@extends('layouts.partner')

@section('title', 'Carteira')
@section('page-title', 'Minha Carteira')

@section('content')
<div class="space-y-6">
    <!-- Alerta se não tiver CPF/Telefone -->
    @if(!auth()->user()->cpf || !auth()->user()->phone)
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded" role="alert">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                <div>
                    <p class="font-semibold">Atenção: Pagamentos PIX indisponíveis</p>
                    <p class="text-sm mt-1">
                        Para utilizar pagamentos via PIX, é necessário cadastrar seu 
                        @if(!auth()->user()->cpf) <strong>CPF</strong> @endif
                        @if(!auth()->user()->cpf && !auth()->user()->phone) e @endif
                        @if(!auth()->user()->phone) <strong>Telefone</strong> @endif
                        no <a href="{{ route('partner.profile') }}" class="underline font-semibold">seu perfil</a>.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Balance Card -->
    <div class="bg-gradient-to-r from-green-500 to-green-700 rounded-lg shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-lg opacity-90">Saldo Disponível</p>
                <h2 class="text-5xl font-bold mt-2">R$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2, ',', '.') }}</h2>
                @if(auth()->user()->pix_key)
                <p class="mt-4 text-sm opacity-75">
                    <i class="fas fa-key"></i> Chave PIX: {{ auth()->user()->pix_key }}
                </p>
                @endif
            </div>
            <div class="text-right">
                <button onclick="openDepositModal()" class="bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-green-50 transition mb-2">
                    <i class="fas fa-plus-circle mr-2"></i>Adicionar Saldo
                </button>
                <button onclick="openWithdrawModal()" class="bg-green-800 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-900 transition">
                    <i class="fas fa-arrow-down mr-2"></i>Sacar
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Depositado</p>
                    <h3 class="text-2xl font-bold text-green-600">R$ {{ number_format($stats['total_deposits'] ?? 0, 2, ',', '.') }}</h3>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Gasto</p>
                    <h3 class="text-2xl font-bold text-red-600">R$ {{ number_format($stats['total_spent'] ?? 0, 2, ',', '.') }}</h3>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Transações</p>
                    <h3 class="text-2xl font-bold text-blue-600">{{ $stats['total_transactions'] ?? 0 }}</h3>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Histórico de Transações</h3>
                <div class="flex space-x-2">
                    <select class="border rounded px-3 py-2 text-sm" id="filterType">
                        <option value="">Todos os tipos</option>
                        <option value="deposit">Depósitos</option>
                        <option value="withdrawal">Saques</option>
                        <option value="transfer_out">Pagamentos</option>
                        <option value="featured_payment">Destaque</option>
                    </select>
                    <select class="border rounded px-3 py-2 text-sm" id="filterStatus">
                        <option value="">Todos os status</option>
                        <option value="completed">Concluídas</option>
                        <option value="pending">Pendentes</option>
                        <option value="failed">Falhas</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($transactions ?? [] as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($transaction->type == 'deposit')
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">
                                <i class="fas fa-arrow-down"></i> Depósito
                            </span>
                            @elseif($transaction->type == 'withdrawal')
                            <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded">
                                <i class="fas fa-arrow-up"></i> Saque
                            </span>
                            @elseif($transaction->type == 'transfer_out')
                            <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">
                                <i class="fas fa-handshake"></i> Pagamento
                            </span>
                            @elseif($transaction->type == 'featured_payment')
                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">
                                <i class="fas fa-star"></i> Destaque
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $transaction->description }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($transaction->payment_method == 'card')
                            <i class="fas fa-credit-card"></i> Cartão
                            @elseif($transaction->payment_method == 'pix')
                            <i class="fas fa-qrcode"></i> PIX
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($transaction->status == 'completed')
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">
                                <i class="fas fa-check-circle"></i> Concluída
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
                        <td class="px-6 py-4 text-right font-semibold {{ in_array($transaction->type, ['deposit']) ? 'text-green-600' : 'text-red-600' }}">
                            {{ in_array($transaction->type, ['deposit']) ? '+' : '-' }}R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
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
</div>

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
                    <button type="button" @click="method = 'pix'" :class="method === 'pix' ? 'border-green-600 bg-green-50' : 'border-gray-300'" class="border-2 rounded-lg p-4 text-center hover:border-green-600 transition">
                        <i class="fas fa-qrcode text-2xl mb-2" :class="method === 'pix' ? 'text-green-600' : 'text-gray-600'"></i>
                        <p class="font-medium">PIX</p>
                        <p class="text-xs text-gray-500">Instantâneo</p>
                    </button>
                    <button type="button" @click="method = 'card'" :class="method === 'card' ? 'border-blue-600 bg-blue-50' : 'border-gray-300'" class="border-2 rounded-lg p-4 text-center hover:border-blue-600 transition">
                        <i class="fas fa-credit-card text-2xl mb-2" :class="method === 'card' ? 'text-blue-600' : 'text-gray-600'"></i>
                        <p class="font-medium">Cartão</p>
                        <p class="text-xs text-gray-500">Crédito/Débito</p>
                    </button>
                </div>
            </div>

            <!-- Formulário PIX -->
            <form x-show="method === 'pix'" method="POST" action="{{ route('partner.wallet.deposit') }}">
                @csrf
                <input type="hidden" name="payment_method" value="pix">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Valor</label>
                    <input type="number" name="amount" step="0.01" min="10" placeholder="R$ 0,00" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500" required>
                    <p class="text-xs text-gray-500 mt-1">Valor mínimo: R$ 10,00</p>
                </div>
                
                <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    <i class="fas fa-qrcode mr-2"></i>Gerar QR Code PIX
                </button>
            </form>

            <!-- Formulário Cartão -->
            <form x-show="method === 'card'" id="depositForm" method="POST" action="{{ route('partner.wallet.deposit') }}">
                @csrf
                <input type="hidden" name="payment_method" value="card">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Valor</label>
                    <input type="number" id="depositAmount" name="amount" step="0.01" min="10" placeholder="R$ 0,00" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">Valor mínimo: R$ 10,00</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Informações do Cartão</label>
                    <div id="card-element" class="border rounded-lg p-3 bg-gray-50"></div>
                    <div id="card-errors" class="text-red-500 text-xs mt-2"></div>
                </div>
                
                <button type="submit" id="submitBtn" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    <i class="fas fa-credit-card mr-2"></i>Pagar com Cartão
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
            
            <!-- Indicador de verificação -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <div class="flex items-center justify-center">
                    <i class="fas fa-sync fa-spin text-blue-600 mr-2"></i>
                    <p class="text-sm text-blue-800">Aguardando pagamento...</p>
                </div>
            </div>
            
            <!-- QR Code Image -->
            @if(session('pix_qr_code_url'))
            <div class="bg-white border-2 border-gray-200 rounded-lg p-4 mb-4">
                <img src="{{ session('pix_qr_code_url') }}" alt="QR Code PIX" class="mx-auto" style="width: 250px; height: 250px;">
            </div>
            @endif
            
            <!-- Código Copia e Cola -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Código PIX Copia e Cola</label>
                <div class="flex gap-2">
                    <input type="text" id="pixCode" value="{{ session('pix_qr_code') }}" 
                        class="flex-1 border rounded-lg px-3 py-2 text-sm bg-gray-50 font-mono" readonly>
                    <button onclick="copyPixCode()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            
            <!-- Instruções -->
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
                Após o pagamento, o saldo será creditado automaticamente em alguns segundos.
            </p>
        </div>
    </div>
</div>

<script>
    // Inicia o polling automaticamente quando o modal é exibido
    document.addEventListener('DOMContentLoaded', function() {
        const pixId = '{{ session("pix_id") }}';
        const pixAmount = {{ session('pix_amount') ?? 0 }};
        
        if (pixId && pixAmount > 0) {
            console.log('Iniciando verificação de pagamento PIX:', pixId);
            startPixPolling(pixId, pixAmount);
        }
    });
</script>
@endif

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
            <form method="POST" action="{{ route('partner.wallet.withdraw') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Valor</label>
                    <input type="number" name="amount" step="0.01" min="20" max="{{ auth()->user()->wallet_balance }}" placeholder="R$ 0,00" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">Valor mínimo: R$ 20,00 | Disponível: R$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2, ',', '.') }}</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Chave PIX</label>
                    <input type="text" name="pix_key" value="{{ auth()->user()->pix_key }}" placeholder="CPF, E-mail, Telefone ou Chave Aleatória" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle"></i> O saque será processado em até 24 horas úteis.
                    </p>
                </div>
                
                <button type="submit" class="w-full bg-orange-600 text-white py-3 rounded-lg font-semibold hover:bg-orange-700 transition">
                    <i class="fas fa-check mr-2"></i>Confirmar Saque
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
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

function copyPixCode() {
    const pixCode = document.getElementById('pixCode');
    pixCode.select();
    pixCode.setSelectionRange(0, 99999);
    
    navigator.clipboard.writeText(pixCode.value).then(() => {
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
        btn.classList.add('bg-green-500');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('bg-green-500');
            btn.classList.add('bg-blue-500', 'hover:bg-blue-600');
        }, 2000);
    }).catch(err => {
        alert('Erro ao copiar código PIX');
    });
}

// PIX Payment Polling
let pixPollingInterval = null;
const PIX_POLLING_INTERVAL = 3000; // 3 segundos
const PIX_POLLING_TIMEOUT = 300000; // 5 minutos

function startPixPolling(pixId, amount) {
    if (pixPollingInterval) {
        clearInterval(pixPollingInterval);
    }
    
    let elapsedTime = 0;
    
    pixPollingInterval = setInterval(async () => {
        elapsedTime += PIX_POLLING_INTERVAL;
        
        // Timeout após 5 minutos
        if (elapsedTime >= PIX_POLLING_TIMEOUT) {
            stopPixPolling();
            showPixExpiredMessage();
            return;
        }
        
        try {
            const response = await fetch('{{ route("partner.wallet.check-pix") }}', {
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
                // Pagamento confirmado!
                stopPixPolling();
                showPixSuccessMessage(data.new_balance);
            } else if (data.status === 'EXPIRED' || data.status === 'CANCELLED') {
                // Pagamento expirado ou cancelado
                stopPixPolling();
                showPixExpiredMessage();
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

function showPixSuccessMessage(newBalance) {
    // Fecha o modal PIX
    closePixModal();
    
    // Atualiza o saldo na tela
    const balanceElement = document.querySelector('.text-3xl.font-bold');
    if (balanceElement) {
        balanceElement.textContent = 'R$ ' + parseFloat(newBalance).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    
    // Mostra mensagem de sucesso
    const successDiv = document.createElement('div');
    successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-fade-in';
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
        successDiv.remove();
        // Recarrega a página para atualizar histórico
        window.location.reload();
    }, 3000);
}

function showPixExpiredMessage() {
    closePixModal();
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-fade-in';
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

// Fecha modal PIX e para polling
function closePixModal() {
    const pixModal = document.getElementById('pixModal');
    if (pixModal) {
        pixModal.classList.add('hidden');
        pixModal.classList.remove('flex');
    }
    stopPixPolling();
}

// Stripe Configuration
const stripe = Stripe('{{ config('services.stripe.key') }}');
const elements = stripe.elements();
const cardElement = elements.create('card', {
    style: {
        base: {
            fontSize: '16px',
            color: '#32325d',
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

cardElement.mount('#card-element');

// Handle card errors
cardElement.on('change', function(event) {
    const displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
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
            errorElement.textContent = error.message;
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

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}
</style>
@endsection
