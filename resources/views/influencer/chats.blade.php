@extends('layouts.influencer')

@section('title', 'Conversas')
@section('page-title', 'Mensagens')
@section('page-subtitle', 'Converse com seus parceiros')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Conversations List -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-4 border-b">
                <button onclick="openNewChatModal()" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition mb-3">
                    <i class="fas fa-plus mr-2"></i>Nova Conversa
                </button>
                <div class="relative">
                    <input type="text" placeholder="Buscar conversas..." 
                        class="w-full border rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <div class="divide-y max-h-[600px] overflow-y-auto">
                @forelse($conversations ?? [] as $conversation)
                <a href="{{ route('influencer.chats.show', $conversation->id) }}" 
                    class="flex items-center p-4 hover:bg-gray-50 transition {{ request()->route('id') == $conversation->id ? 'bg-purple-50' : '' }}">
                    <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center text-white font-semibold mr-3 flex-shrink-0">
                        {{ substr($conversation->partner_name, 0, 2) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="font-semibold text-sm truncate">{{ $conversation->partner_name }}</h4>
                            <span class="text-xs text-gray-500">{{ $conversation->last_message_at?->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-600 truncate">{{ $conversation->last_message }}</p>
                    </div>
                    @if($conversation->unread_count > 0)
                    <div class="ml-2 bg-purple-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                        {{ $conversation->unread_count }}
                    </div>
                    @endif
                </a>
                @empty
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-comments text-4xl mb-3"></i>
                    <p class="text-sm">Nenhuma conversa ainda</p>
                    <button onclick="openNewChatModal()" class="mt-4 bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-plus mr-2"></i>Iniciar Conversa
                    </button>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm h-[600px] flex flex-col">
            @if(isset($activeChat))
            <!-- Chat Header -->
            <div class="p-4 border-b flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                        {{ substr($activeChat->partner_name, 0, 2) }}
                    </div>
                    <div>
                        <h3 class="font-semibold">{{ $activeChat->partner_name }}</h3>
                        <p class="text-xs text-gray-500">{{ $activeChat->place_name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="openProposalModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition text-sm font-semibold">
                        <i class="fas fa-handshake mr-2"></i>Enviar Proposta
                    </button>
                    <button class="text-gray-600 hover:text-purple-600 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @forelse($messages ?? [] as $message)
                <div class="flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-xs lg:max-w-md">
                        @if($message->sender_id != auth()->id())
                        <div class="flex items-start space-x-2">
                            <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white text-xs flex-shrink-0">
                                {{ substr($activeChat->partner_name, 0, 2) }}
                            </div>
                            <div>
                                <div class="bg-gray-100 rounded-lg rounded-tl-none p-3">
                                    <p class="text-sm">{{ $message->message }}</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 ml-2">{{ $message->created_at->format('H:i') }}</p>
                            </div>
                        </div>
                        @else
                        <div>
                            <div class="bg-purple-600 text-white rounded-lg rounded-tr-none p-3">
                                <p class="text-sm">{{ $message->message }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 mr-2 text-right">
                                {{ $message->created_at->format('H:i') }}
                                @if($message->read_at)
                                <i class="fas fa-check-double text-blue-500"></i>
                                @else
                                <i class="fas fa-check"></i>
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="flex items-center justify-center h-full text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-comments text-4xl mb-3"></i>
                        <p>Nenhuma mensagem ainda</p>
                        <p class="text-sm mt-1">Envie a primeira mensagem!</p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Message Input -->
            <div class="p-4 border-t">
                <form method="POST" action="{{ route('influencer.chats.send', $activeChat->id) }}" class="flex items-center space-x-2">
                    @csrf
                    <button type="button" class="text-gray-600 hover:text-purple-600 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-paperclip text-xl"></i>
                    </button>
                    <input type="text" name="message" placeholder="Digite sua mensagem..." 
                        class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
            @else
            <!-- No chat selected -->
            <div class="flex items-center justify-center h-full text-gray-500">
                <div class="text-center">
                    <i class="fas fa-comments text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Selecione uma conversa</h3>
                    <p class="text-sm">Escolha uma conversa ao lado para começar</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div id="newChatModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b bg-purple-600 text-white flex items-center justify-between sticky top-0 z-10">
            <h3 class="text-2xl font-bold">
                <i class="fas fa-comments mr-2"></i>Iniciar Nova Conversa
            </h3>
            <button onclick="closeNewChatModal()" class="text-white hover:text-gray-200 p-2 rounded-lg transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div class="mb-4">
                <input type="text" id="placeSearch" placeholder="Buscar estabelecimento..." 
                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div id="placesList" class="space-y-3">
                <!-- Will be populated via AJAX -->
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-spinner fa-spin text-3xl mb-3 text-purple-600"></i>
                    <p>Carregando estabelecimentos...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Proposal Modal -->
<div id="proposalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b bg-purple-600 text-white flex items-center justify-between">
            <h3 class="text-xl font-bold">
                <i class="fas fa-handshake mr-2"></i>Enviar Proposta
            </h3>
            <button onclick="closeProposalModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="proposalForm" method="POST" action="{{ isset($activeChat) ? route('influencer.proposals.send', $activeChat->id) : '#' }}">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Estabelecimento</label>
                    <input type="text" value="{{ isset($activeChat) ? $activeChat->partner_name : '' }}" readonly class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 bg-gray-50">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mensagem *</label>
                    <textarea name="message" rows="4" required placeholder="Descreva sua proposta..." class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Valor do Pagamento (R$) *</label>
                    <input type="number" name="payment_amount" step="0.01" min="0" required placeholder="0,00" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Data de Entrega</label>
                    <input type="date" name="delivery_date" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>
            
            <div class="p-6 border-t flex justify-end space-x-3">
                <button type="button" onclick="closeProposalModal()" class="px-6 py-2 border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition font-semibold">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    <i class="fas fa-paper-plane mr-2"></i>Enviar Proposta
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto scroll to bottom of messages
const messagesContainer = document.querySelector('.overflow-y-auto');
if (messagesContainer && messagesContainer.children.length > 0) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function openProposalModal() {
    const modal = document.getElementById('proposalModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeProposalModal() {
    const modal = document.getElementById('proposalModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('proposalForm').reset();
}

function openNewChatModal() {
    const modal = document.getElementById('newChatModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    loadPlaces();
}

function closeNewChatModal() {
    const modal = document.getElementById('newChatModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function loadPlaces() {
    const list = document.getElementById('placesList');
    
    console.log('Carregando estabelecimentos...');
    
    fetch('/influencer/chats/places', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Resposta recebida:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Dados recebidos:', data);
        if (data.places && data.places.length > 0) {
            list.innerHTML = data.places.map(place => `
                <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-purple-500 transition cursor-pointer" onclick="startChat(${place.id})">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center overflow-hidden border-2 border-purple-600">
                                ${place.image 
                                    ? `<img src="${place.image}" alt="${place.name}" class="w-full h-full object-cover">`
                                    : `<div class="w-full h-full bg-purple-600 flex items-center justify-center text-white font-bold">${place.name.substring(0, 2)}</div>`
                                }
                            </div>
                            <div>
                                <h4 class="font-bold text-lg">${place.name}</h4>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-map-marker-alt mr-1"></i>${place.city || 'Cidade não informada'}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-700">
                                <i class="fas fa-video text-blue-600 mr-1"></i>${place.videos_count || 0} vídeos
                            </div>
                            <div class="text-sm text-gray-600">
                                ${place.category || 'Sem categoria'}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            list.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-store-slash text-4xl mb-3 text-gray-300"></i>
                    <p>Nenhum estabelecimento disponível</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading places:', error);
        list.innerHTML = `
            <div class="text-center py-8 text-red-500">
                <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                <p>Erro ao carregar estabelecimentos</p>
            </div>
        `;
    });
}

function startChat(placeId) {
    fetch('/influencer/chats/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ place_id: placeId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `/influencer/chats/${data.chat_id}`;
        } else {
            alert(data.message || 'Erro ao iniciar conversa');
        }
    })
    .catch(error => {
        console.error('Error starting chat:', error);
        alert('Erro ao iniciar conversa');
    });
}

// Auto refresh messages every 5 seconds
@if(isset($activeChat))
setInterval(function() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMessages = doc.querySelector('.overflow-y-auto');
            if (newMessages && messagesContainer) {
                messagesContainer.innerHTML = newMessages.innerHTML;
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        })
        .catch(error => console.error('Error refreshing messages:', error));
}, 5000);
@endif
</script>
@endpush
