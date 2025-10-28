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
                    <button class="text-gray-600 hover:text-purple-600 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-phone"></i>
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
@endsection

@push('scripts')
<script>
// Auto scroll to bottom of messages
const messagesContainer = document.querySelector('.overflow-y-auto');
if (messagesContainer && messagesContainer.children.length > 0) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
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
