@extends('layouts.influencer')

@section('title', 'Vídeos')
@section('page-title', 'Meus Vídeos')
@section('page-subtitle', 'Gerencie e publique seus vídeos')

@section('content')
<!-- Upload Button & Stats -->
<div class="flex items-center justify-between mb-8">
    <div class="flex items-center space-x-4">
        <button onclick="openUploadModal()" class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-6 py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-purple-800 transition shadow-lg">
            <i class="fas fa-upload mr-2"></i>Enviar Novo Vídeo
        </button>
    </div>
    
    <div class="flex items-center space-x-6 text-sm">
        <div class="text-center">
            <p class="text-gray-500">Total de Vídeos</p>
            <p class="text-2xl font-bold text-purple-600">{{ $totalVideos ?? 0 }}</p>
        </div>
        <div class="text-center border-l pl-6">
            <p class="text-gray-500">Visualizações</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($totalViews ?? 0) }}</p>
        </div>
        <div class="text-center border-l pl-6">
            <p class="text-gray-500">Ganhos Totais</p>
            <p class="text-2xl font-bold text-green-600">R$ {{ number_format($totalEarnings ?? 0, 2, ',', '.') }}</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <button class="px-4 py-2 rounded-lg font-medium transition {{ request('status', 'all') == 'all' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Todos
            </button>
            <button class="px-4 py-2 rounded-lg font-medium transition {{ request('status') == 'published' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Publicados
            </button>
            <button class="px-4 py-2 rounded-lg font-medium transition {{ request('status') == 'pending' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Pendentes
            </button>
            <button class="px-4 py-2 rounded-lg font-medium transition {{ request('status') == 'draft' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Rascunhos
            </button>
        </div>
        
        <select class="border rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <option>Mais recentes</option>
            <option>Mais antigos</option>
            <option>Mais visualizados</option>
            <option>Maior engajamento</option>
        </select>
    </div>
</div>

<!-- Videos Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($videos ?? [] as $video)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition group">
        <!-- Thumbnail -->
        <div class="relative aspect-video bg-gray-200 overflow-hidden">
            @if($video->thumbnail_url)
            <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
            @else
            <div class="w-full h-full flex items-center justify-center">
                <i class="fas fa-video text-gray-400 text-5xl"></i>
            </div>
            @endif
            
            <!-- Play overlay -->
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition flex items-center justify-center">
                <div class="opacity-0 group-hover:opacity-100 transition">
                    <button class="bg-white rounded-full w-16 h-16 flex items-center justify-center text-purple-600 hover:bg-purple-600 hover:text-white transition shadow-lg">
                        <i class="fas fa-play ml-1 text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Duration -->
            @if($video->duration)
            <div class="absolute bottom-2 right-2 bg-black bg-opacity-80 text-white text-xs px-2 py-1 rounded">
                {{ gmdate('i:s', $video->duration) }}
            </div>
            @endif
            
            <!-- Status Badge -->
            <div class="absolute top-2 left-2">
                @if($video->status == 'published')
                <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                    <i class="fas fa-check-circle"></i> Publicado
                </span>
                @elseif($video->status == 'pending')
                <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                    <i class="fas fa-clock"></i> Pendente
                </span>
                @else
                <span class="bg-gray-500 text-white text-xs px-2 py-1 rounded-full">
                    <i class="fas fa-file"></i> Rascunho
                </span>
                @endif
            </div>
        </div>
        
        <!-- Video Info -->
        <div class="p-4">
            <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">{{ $video->title }}</h3>
            
            <div class="flex items-center space-x-4 text-sm text-gray-500 mb-3">
                <span><i class="fas fa-eye mr-1"></i>{{ number_format($video->views ?? 0) }}</span>
                <span><i class="fas fa-heart mr-1"></i>{{ number_format($video->likes ?? 0) }}</span>
                <span><i class="fas fa-comment mr-1"></i>{{ number_format($video->comments ?? 0) }}</span>
            </div>
            
            @if($video->place_name)
            <div class="flex items-center text-sm text-purple-600 mb-3">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <span>{{ $video->place_name }}</span>
            </div>
            @endif
            
            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                <span>{{ $video->created_at->diffForHumans() }}</span>
                @if($video->payment_amount)
                <span class="text-green-600 font-semibold">
                    <i class="fas fa-dollar-sign"></i> R$ {{ number_format($video->payment_amount, 2, ',', '.') }}
                </span>
                @endif
            </div>
            
            <!-- Actions -->
            <div class="flex items-center space-x-2 pt-3 border-t">
                <button onclick="openBoostModal({{ $video->id }}, '{{ $video->title }}')" class="flex-1 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                    <i class="fas fa-rocket mr-1"></i>Impulsionar
                </button>
                <button class="flex-1 bg-purple-50 hover:bg-purple-100 text-purple-600 px-3 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-chart-line mr-1"></i>Stats
                </button>
                <button class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 bg-white rounded-lg shadow-sm p-12 text-center">
        <i class="fas fa-video-slash text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhum vídeo publicado ainda</h3>
        <p class="text-gray-500 mb-6">Comece enviando seu primeiro vídeo e ganhe com suas criações!</p>
        <button onclick="openUploadModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition">
            <i class="fas fa-upload mr-2"></i>Enviar Primeiro Vídeo
        </button>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if(isset($videos) && $videos->hasPages())
<div class="mt-6">
    {{ $videos->links() }}
</div>
@endif
@endsection

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white z-10">
            <h3 class="text-xl font-semibold">
                <i class="fas fa-upload text-purple-600 mr-2"></i>Enviar Novo Vídeo
            </h3>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ route('influencer.videos.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Video File -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Arquivo do Vídeo *</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-purple-500 transition">
                        <input type="file" name="video" id="videoFile" accept="video/*" class="hidden" required>
                        <label for="videoFile" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                            <p class="text-sm text-gray-600">Clique para selecionar ou arraste o vídeo</p>
                            <p class="text-xs text-gray-500 mt-1">MP4, MOV, AVI - Máx. 500MB</p>
                        </label>
                    </div>
                </div>
                
                <!-- Title -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Título do Vídeo *</label>
                    <input type="text" name="title" 
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                        placeholder="Ex: Conhecendo o melhor restaurante de São Paulo" required>
                </div>
                
                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Descrição</label>
                    <textarea name="description" rows="4" 
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                        placeholder="Conte mais sobre este vídeo..."></textarea>
                </div>
                
                <!-- Place (from accepted proposals) -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Estabelecimento</label>
                    <select name="place_id" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Selecione (opcional)</option>
                        @foreach($acceptedProposals ?? [] as $proposal)
                        <option value="{{ $proposal->place_id }}">{{ $proposal->place_name }} - R$ {{ number_format($proposal->payment_amount, 2, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Vincule este vídeo a uma proposta aceita para receber o pagamento</p>
                </div>
                
                <!-- Tags -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Tags</label>
                    <input type="text" name="tags" 
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                        placeholder="viagem, restaurante, turismo (separadas por vírgula)">
                </div>
                
                <!-- Thumbnail -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Miniatura (Thumbnail)</label>
                    <input type="file" name="thumbnail" accept="image/*" 
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Imagem de capa do vídeo. Se não enviar, será gerado automaticamente.</p>
                </div>
                
                <!-- Visibility -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Visibilidade</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="status" value="published" class="mr-2" checked>
                            <span class="text-sm">
                                <i class="fas fa-globe text-green-600 mr-2"></i>
                                <strong>Publicar Imediatamente</strong> - O vídeo ficará visível para todos
                            </span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="status" value="draft" class="mr-2">
                            <span class="text-sm">
                                <i class="fas fa-file text-gray-600 mr-2"></i>
                                <strong>Salvar como Rascunho</strong> - Você poderá publicar depois
                            </span>
                        </label>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Dica:</strong> Vídeos vinculados a propostas aceitas geram pagamento automaticamente após aprovação do parceiro.
                    </p>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeUploadModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold transition">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-upload mr-2"></i>Enviar Vídeo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Boost Modal -->
<div id="boostModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-xl font-semibold">
                <i class="fas fa-rocket text-yellow-500 mr-2"></i>Impulsionar Vídeo
            </h3>
            <button onclick="closeBoostModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ route('influencer.videos.boost') }}" id="boostForm">
                @csrf
                <input type="hidden" name="video_id" id="boostVideoId">
                
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-700 mb-2">
                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                        <strong>Vídeo:</strong> <span id="boostVideoTitle"></span>
                    </p>
                    <p class="text-xs text-gray-600">
                        Impulsione este vídeo para alcançar mais pessoas e aumentar suas visualizações!
                    </p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Valor Total do Investimento</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">R$</span>
                        <input type="number" name="amount" id="boostAmount" step="0.01" min="10" 
                            class="w-full border rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-transparent" 
                            placeholder="0,00" required oninput="calculateDailyBudget()">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Saldo disponível: R$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Duração da Campanha</label>
                    <select name="days" id="boostDays" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-transparent" required onchange="calculateDailyBudget()">
                        <option value="">Selecione...</option>
                        <option value="1">1 dia</option>
                        <option value="3">3 dias</option>
                        <option value="7">7 dias</option>
                        <option value="14">14 dias</option>
                        <option value="30">30 dias</option>
                    </select>
                </div>
                
                <div id="budgetInfo" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 hidden">
                    <p class="text-sm text-blue-900 font-medium mb-2">
                        <i class="fas fa-calculator mr-2"></i>Cálculo do Budget Diário
                    </p>
                    <div class="text-sm text-blue-800">
                        <p class="mb-1">• Valor Total: R$ <span id="totalAmount">0,00</span></p>
                        <p class="mb-1">• Duração: <span id="campaignDays">0</span> dia(s)</p>
                        <p class="font-bold text-lg text-blue-900 mt-2">
                            • Budget Diário: R$ <span id="dailyBudget">0,00</span>
                        </p>
                    </div>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-green-800">
                        <i class="fas fa-chart-line mr-1"></i>
                        <strong>Como funciona:</strong><br>
                        • Seu vídeo aparecerá em destaque<br>
                        • Métricas CPC, CTR e impressões serão rastreadas<br>
                        • Você pode pausar a campanha a qualquer momento
                    </p>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white py-3 rounded-lg font-semibold transition shadow-lg">
                    <i class="fas fa-rocket mr-2"></i>Impulsionar Agora
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
    document.getElementById('uploadModal').classList.add('flex');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadModal').classList.remove('flex');
}

function openBoostModal(videoId, videoTitle) {
    document.getElementById('boostVideoId').value = videoId;
    document.getElementById('boostVideoTitle').textContent = videoTitle;
    document.getElementById('boostModal').classList.remove('hidden');
    document.getElementById('boostModal').classList.add('flex');
}

function closeBoostModal() {
    document.getElementById('boostModal').classList.add('hidden');
    document.getElementById('boostModal').classList.remove('flex');
    document.getElementById('boostForm').reset();
    document.getElementById('budgetInfo').classList.add('hidden');
}

function calculateDailyBudget() {
    const amount = parseFloat(document.getElementById('boostAmount').value) || 0;
    const days = parseInt(document.getElementById('boostDays').value) || 0;
    
    if (amount > 0 && days > 0) {
        const dailyBudget = amount / days;
        
        document.getElementById('totalAmount').textContent = amount.toFixed(2).replace('.', ',');
        document.getElementById('campaignDays').textContent = days;
        document.getElementById('dailyBudget').textContent = dailyBudget.toFixed(2).replace('.', ',');
        document.getElementById('budgetInfo').classList.remove('hidden');
    } else {
        document.getElementById('budgetInfo').classList.add('hidden');
    }
}

// Preview video file name
const videoFileInput = document.getElementById('videoFile');
if (videoFileInput) {
    videoFileInput.addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            const label = e.target.nextElementSibling;
            label.querySelector('p').textContent = fileName;
            label.querySelector('i').className = 'fas fa-video text-4xl text-purple-600 mb-3';
        }
    });
}
</script>
@endpush
