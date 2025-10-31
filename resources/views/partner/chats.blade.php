@extends('layouts.partner')

@section('title', 'Conversas')
@section('page-title', 'Conversas com Influenciadores')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Conversations List -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md border-2 border-[#FEB800]">
            <div class="p-4 border-b bg-[#FEB800]">
                <button onclick="openNewChatModal()" class="w-full bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition mb-3">
                    <i class="fas fa-plus mr-2"></i>Nova Conversa
                </button>
                <div class="relative">
                    <input type="text" placeholder="Buscar conversas..." 
                        class="w-full border-2 border-black rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-black">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-600"></i>
                </div>
            </div>
            
            <div class="divide-y max-h-[600px] overflow-y-auto">
                @forelse($conversations ?? [] as $conversation)
                <a href="{{ route('partner.chats.show', $conversation->id) }}" 
                    class="flex items-center p-4 hover:bg-[#FEB800] hover:bg-opacity-10 transition {{ request()->route('id') == $conversation->id ? 'bg-[#FEB800] bg-opacity-20' : '' }}">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mr-3 flex-shrink-0 overflow-hidden border-2 border-black">
                        @if($conversation->influencer_avatar)
                            <img src="{{ $conversation->influencer_avatar }}" alt="{{ $conversation->influencer_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-[#FEB800] flex items-center justify-center text-black font-bold">
                                {{ substr($conversation->influencer_name, 0, 2) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="font-semibold text-sm truncate">{{ $conversation->influencer_name }}</h4>
                            <span class="text-xs text-gray-500">{{ $conversation->last_message_at?->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-600 truncate">{{ $conversation->last_message }}</p>
                    </div>
                    @if($conversation->unread_count > 0)
                    <div class="ml-2 bg-black text-[#FEB800] text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                        {{ $conversation->unread_count }}
                    </div>
                    @endif
                </a>
                @empty
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-comments text-4xl mb-3 text-[#FEB800]"></i>
                    <p class="text-sm">Nenhuma conversa ainda</p>
                    <button onclick="openNewChatModal()" class="mt-4 bg-[#FEB800] text-black px-6 py-2 rounded-lg hover:bg-black hover:text-white border-2 border-black transition font-semibold">
                        <i class="fas fa-plus mr-2"></i>Iniciar Conversa
                    </button>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md border-2 border-[#FEB800] h-[700px] flex flex-col">
            @if(isset($activeChat))
            <!-- Chat Header -->
            <div class="p-4 border-b bg-[#FEB800] flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 overflow-hidden border-2 border-black">
                        @if($activeChat->influencer_avatar)
                            <img src="{{ $activeChat->influencer_avatar }}" alt="{{ $activeChat->influencer_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-black flex items-center justify-center text-[#FEB800] font-bold">
                                {{ substr($activeChat->influencer_name, 0, 2) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-black">{{ $activeChat->influencer_name }}</h3>
                        <p class="text-xs text-gray-700">Influenciador</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button class="text-black hover:text-white hover:bg-black p-2 rounded-lg transition">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <div id="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                @forelse($messages ?? [] as $message)
                <div class="flex {{ $message->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-xs lg:max-w-md">
                        @if($message->sender_id != auth()->id())
                        <div class="flex items-start space-x-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs flex-shrink-0 overflow-hidden border-2 border-black">
                                @if($activeChat->influencer_avatar)
                                    <img src="{{ $activeChat->influencer_avatar }}" alt="{{ $activeChat->influencer_name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-[#FEB800] flex items-center justify-center text-black font-bold">
                                        {{ substr($activeChat->influencer_name, 0, 2) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="bg-white border-2 border-gray-300 rounded-lg rounded-tl-none p-3 shadow">
                                    <p class="text-sm text-gray-800">{{ $message->message }}</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 ml-2">{{ $message->created_at->format('H:i') }}</p>
                            </div>
                        </div>
                        @else
                        <div>
                            <div class="bg-[#FEB800] border-2 border-black text-black rounded-lg rounded-tr-none p-3 shadow">
                                <p class="text-sm font-medium">{{ $message->message }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 mr-2 text-right">
                                {{ $message->created_at->format('H:i') }}
                                @if($message->read_at)
                                <i class="fas fa-check-double text-blue-600"></i>
                                @else
                                <i class="fas fa-check text-gray-400"></i>
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="flex items-center justify-center h-full">
                    <div class="text-center text-gray-400">
                        <i class="fas fa-comments text-6xl mb-4 text-[#FEB800] opacity-50"></i>
                        <p>Nenhuma mensagem ainda</p>
                        <p class="text-sm mt-2">Envie uma mensagem para iniciar a conversa</p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pending Proposals Section -->
            @if(isset($proposals) && $proposals->count() > 0)
            <div class="border-t bg-yellow-50 p-4">
                <h4 class="font-bold text-sm text-gray-800 mb-3">
                    <i class="fas fa-handshake text-yellow-600 mr-2"></i>Propostas Pendentes ({{ $proposals->count() }})
                </h4>
                <div class="space-y-3 max-h-48 overflow-y-auto">
                    @foreach($proposals as $proposal)
                    <div class="bg-white border-2 border-yellow-300 rounded-lg p-3">
                        <p class="text-sm text-gray-700 mb-2">{{ $proposal->message }}</p>
                        <div class="flex items-center justify-between">
                            <div class="text-sm">
                                <span class="font-bold text-green-600">R$ {{ number_format($proposal->payment_amount, 2, ',', '.') }}</span>
                                @if($proposal->delivery_date)
                                <span class="text-gray-500 ml-2">
                                    <i class="fas fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($proposal->delivery_date)->format('d/m/Y') }}
                                </span>
                                @endif
                            </div>
                            <div class="flex space-x-2">
                                <form method="POST" action="{{ route('partner.proposals.accept', $proposal->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-semibold">
                                        <i class="fas fa-check mr-1"></i>Aceitar
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('partner.proposals.reject', $proposal->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold">
                                        <i class="fas fa-times mr-1"></i>Recusar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Message Input -->
            <div class="p-4 border-t bg-white">
                <form action="{{ route('partner.chats.send', $activeChat->id) }}" method="POST" class="flex items-center space-x-2">
                    @csrf
                    <input type="text" name="message" placeholder="Digite sua mensagem..." 
                        class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#FEB800] focus:border-[#FEB800]"
                        required>
                    <button type="submit" class="bg-[#FEB800] text-black px-6 py-2 rounded-lg hover:bg-black hover:text-white border-2 border-black transition font-semibold">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar
                    </button>
                </form>
            </div>
            @else
            <!-- No Chat Selected -->
            <div class="flex items-center justify-center h-full">
                <div class="text-center text-gray-400">
                    <i class="fas fa-comments text-6xl mb-4 text-[#FEB800] opacity-50"></i>
                    <p class="text-lg font-semibold">Selecione uma conversa</p>
                    <p class="text-sm mt-2">Escolha uma conversa da lista ou inicie uma nova</p>
                    <button onclick="openNewChatModal()" class="mt-4 bg-[#FEB800] text-black px-6 py-3 rounded-lg hover:bg-black hover:text-white border-2 border-black transition font-semibold">
                        <i class="fas fa-plus mr-2"></i>Nova Conversa
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div id="newChatModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto border-4 border-[#FEB800]">
        <div class="p-6 border-b bg-[#FEB800] flex items-center justify-between sticky top-0 z-10">
            <h3 class="text-2xl font-bold text-black">
                <i class="fas fa-user-plus mr-2"></i>Iniciar Nova Conversa
            </h3>
            <button onclick="closeNewChatModal()" class="text-black hover:text-white hover:bg-black p-2 rounded-lg transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div class="mb-4">
                <input type="text" id="influencerSearch" placeholder="Buscar influenciador..." 
                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#FEB800] focus:border-[#FEB800]">
            </div>
            
            <div id="influencersList" class="space-y-3">
                <!-- Will be populated via AJAX -->
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-spinner fa-spin text-3xl mb-3 text-[#FEB800]"></i>
                    <p>Carregando influenciadores...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openNewChatModal() {
    const modal = document.getElementById('newChatModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    loadInfluencers();
}

function closeNewChatModal() {
    const modal = document.getElementById('newChatModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function loadInfluencers() {
    const list = document.getElementById('influencersList');
    
    console.log('Carregando influenciadores...');
    
    fetch('/partner/chats/influencers', {
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
        if (data.influencers && data.influencers.length > 0) {
            list.innerHTML = data.influencers.map(inf => `
                <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-[#FEB800] transition cursor-pointer" onclick="startChat(${inf.id})">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center overflow-hidden border-2 border-black">
                                ${inf.avatar 
                                    ? `<img src="${inf.avatar}" alt="${inf.name}" class="w-full h-full object-cover">`
                                    : `<div class="w-full h-full bg-[#FEB800] flex items-center justify-center text-black font-bold">${inf.name.substring(0, 2)}</div>`
                                }
                            </div>
                            <div>
                                <h4 class="font-bold text-lg">${inf.name}</h4>
                                <p class="text-sm text-gray-600">@${inf.username || 'sem-username'}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-700">
                                <i class="fas fa-video text-blue-600 mr-1"></i>${inf.videos_count || 0} vídeos
                            </div>
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-eye text-green-600 mr-1"></i>${inf.total_views || 0} views
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            list.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-user-slash text-4xl mb-3 text-gray-300"></i>
                    <p>Nenhum influenciador disponível</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading influencers:', error);
        list.innerHTML = `
            <div class="text-center py-8 text-red-500">
                <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                <p>Erro ao carregar influenciadores</p>
            </div>
        `;
    });
}

function startChat(influencerId) {
    fetch('/partner/chats/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ influencer_id: influencerId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `/partner/chats/${data.chat_id}`;
        } else {
            alert(data.message || 'Erro ao iniciar conversa');
        }
    })
    .catch(error => {
        console.error('Error starting chat:', error);
        alert('Erro ao iniciar conversa');
    });
}

// Auto scroll to bottom of messages
@if(isset($activeChat))
const messagesContainer = document.getElementById('messagesContainer');
if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}
@endif
</script>
@endpush
@endsection
