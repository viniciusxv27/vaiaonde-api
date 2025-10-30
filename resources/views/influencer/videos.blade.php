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
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition flex items-center justify-center cursor-pointer" onclick="openVideoPlayer('{{ $video->video_url }}', '{{ $video->title }}')">
                <div class="opacity-0 group-hover:opacity-100 transition">
                    <button type="button" class="bg-white rounded-full w-16 h-16 flex items-center justify-center text-purple-600 hover:bg-purple-600 hover:text-white transition shadow-lg">
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
                @if($video->active)
                <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                    <i class="fas fa-check-circle"></i> Publicado
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
                <span><i class="fas fa-eye mr-1"></i>{{ number_format($video->views_count ?? 0) }}</span>
                <span><i class="fas fa-heart mr-1"></i>{{ number_format($video->likes_count ?? 0) }}</span>
                <span><i class="fas fa-share mr-1"></i>{{ number_format($video->shares_count ?? 0) }}</span>
            </div>
            
            @if($video->place)
            <div class="flex items-center text-sm text-purple-600 mb-3">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <span>{{ $video->place->name }}</span>
            </div>
            @endif
            
            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                <span>{{ $video->created_at->diffForHumans() }}</span>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center space-x-2 pt-3 border-t">
                @if($video->activeBoost)
                <!-- Vídeo já está impulsionado - Mostrar métricas -->
                <button onclick="openBoostMetricsModal({{ $video->id }}, '{{ $video->title }}', {{ json_encode($video->activeBoost) }})" class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                    <i class="fas fa-chart-line mr-1"></i>Ver Métricas
                </button>
                @else
                <!-- Vídeo não está impulsionado - Mostrar opção de impulsionar -->
                <button onclick="openBoostModal({{ $video->id }}, '{{ $video->title }}')" class="flex-1 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                    <i class="fas fa-rocket mr-1"></i>Impulsionar
                </button>
                @endif
                <a href="{{ route('influencer.videos.edit', $video->id) }}" class="flex-1 bg-purple-50 hover:bg-purple-100 text-purple-600 px-3 py-2 rounded-lg text-sm font-medium transition text-center">
                    <i class="fas fa-edit mr-1"></i>Editar
                </a>
                <form method="POST" action="{{ route('influencer.videos.delete', $video->id) }}" onsubmit="return confirm('Tem certeza que deseja excluir este vídeo?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-2 rounded-lg text-sm transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
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

<!-- Video Player Modal -->
<div id="videoPlayerModal" class="fixed inset-0 bg-black bg-opacity-90 hidden items-center justify-center z-50" onclick="closeVideoPlayer()">
    <div class="relative w-full max-w-4xl mx-4" onclick="event.stopPropagation()">
        <button onclick="closeVideoPlayer()" class="absolute -top-10 right-0 text-white hover:text-gray-300 text-xl bg-black bg-opacity-50 rounded-full w-8 h-8 flex items-center justify-center">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="bg-black rounded-lg overflow-hidden shadow-2xl">
            <div class="relative" style="padding-bottom: 56.25%; /* 16:9 aspect ratio */">
                <video id="videoPlayer" controls class="absolute inset-0 w-full h-full" controlsList="nodownload">
                    <source id="videoSource" src="" type="video/mp4">
                    Seu navegador não suporta a reprodução de vídeos.
                </video>
            </div>
            <div class="p-3 bg-gray-900 text-white">
                <h3 id="videoPlayerTitle" class="text-base font-semibold truncate"></h3>
            </div>
        </div>
    </div>
</div>

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
            <!-- Aviso sobre limite temporário -->
            <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
                    <div class="text-sm">
                        <p class="font-semibold text-yellow-800 mb-1">⚠️ Limite Temporário: Vídeos até 20MB</p>
                        <p class="text-yellow-700">
                            Você está usando o servidor de desenvolvimento do Laravel. Para fazer upload de vídeos maiores (até 500MB), 
                            configure Apache, Nginx ou Laravel Valet. Veja instruções em <code class="bg-yellow-100 px-1 rounded">UPLOAD_SOLUTION.md</code>
                        </p>
                    </div>
                </div>
            </div>
            
            <form id="uploadVideoForm" method="POST" action="{{ route('influencer.videos.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Video File -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Arquivo do Vídeo *</label>
                    <input type="file" name="video" id="videoFile" accept="video/*" class="hidden" required>
                    <div id="videoDropArea" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-purple-500 transition cursor-pointer" onclick="document.getElementById('videoFile').click()">
                        <div id="videoLabel">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3 block"></i>
                            <p class="text-sm text-gray-600">Clique para selecionar ou arraste o vídeo</p>
                            <p class="text-xs text-gray-500 mt-1">MP4, MOV, AVI - Máx. 20MB (temporário)</p>
                        </div>
                    </div>
                    <!-- Video Preview -->
                    <div id="videoPreviewContainer" class="hidden mt-4">
                        <label class="block text-sm font-medium mb-2">Preview do Vídeo</label>
                        <video id="videoPreview" controls class="w-full rounded-lg shadow-lg max-h-96"></video>
                        <div class="mt-2 flex items-center justify-between text-sm">
                            <span id="videoFileName" class="text-gray-600"></span>
                            <button type="button" onclick="clearVideoPreview()" class="text-red-600 hover:text-red-700">
                                <i class="fas fa-times mr-1"></i>Remover
                            </button>
                        </div>
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
                        <option value="{{ $proposal->place_id }}">{{ $proposal->place->name ?? 'Estabelecimento' }} - R$ {{ number_format($proposal->amount, 2, ',', '.') }}</option>
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
                    <input type="file" name="thumbnail" id="thumbnailFile" accept="image/*" 
                        class="hidden">
                    <label for="thumbnailFile" class="block border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-purple-500 transition cursor-pointer">
                        <i class="fas fa-image text-2xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">Clique para selecionar a miniatura</p>
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG - Recomendado 1280x720px</p>
                    </label>
                    <!-- Thumbnail Preview -->
                    <div id="thumbnailPreviewContainer" class="hidden mt-4">
                        <label class="block text-sm font-medium mb-2">Preview da Thumbnail</label>
                        <div class="relative inline-block">
                            <img id="thumbnailPreview" class="rounded-lg shadow-lg max-h-48">
                            <button type="button" onclick="clearThumbnailPreview()" class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
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
                    <button type="submit" id="submitBtn" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        <span id="submitBtnText">
                            <i class="fas fa-upload mr-2"></i>Enviar Vídeo
                        </span>
                        <span id="submitBtnLoading" class="hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Enviando...
                        </span>
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

<!-- Boost Metrics Modal -->
<div id="boostMetricsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white z-10">
            <h3 class="text-xl font-semibold">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i>Métricas do Impulsionamento
            </h3>
            <button onclick="closeBoostMetricsModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <!-- Video Info -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-gray-800 mb-2" id="metricsVideoTitle"></h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Status:</span>
                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium" id="metricsStatus">
                            <i class="fas fa-check-circle"></i> Ativo
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-600">Período:</span>
                        <span class="ml-2 text-gray-800 font-medium" id="metricsPeriod"></span>
                    </div>
                </div>
            </div>

            <!-- Investment Summary -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Investimento Total</span>
                        <i class="fas fa-dollar-sign text-yellow-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-yellow-700" id="metricsTotalAmount">R$ 0,00</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Budget Diário</span>
                        <i class="fas fa-calendar-day text-blue-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-blue-700" id="metricsDailyBudget">R$ 0,00</p>
                </div>
                
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Gasto Atual</span>
                        <i class="fas fa-chart-pie text-purple-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-purple-700" id="metricsSpent">R$ 0,00</p>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="mb-6">
                <h5 class="text-lg font-semibold mb-4 text-gray-800">
                    <i class="fas fa-chart-bar mr-2"></i>Desempenho da Campanha
                </h5>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Impressões</span>
                            <i class="fas fa-eye text-gray-400"></i>
                        </div>
                        <p class="text-3xl font-bold text-gray-800" id="metricsImpressions">0</p>
                        <p class="text-xs text-gray-500 mt-1">Visualizações do anúncio</p>
                    </div>
                    
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Cliques</span>
                            <i class="fas fa-mouse-pointer text-gray-400"></i>
                        </div>
                        <p class="text-3xl font-bold text-gray-800" id="metricsClicks">0</p>
                        <p class="text-xs text-gray-500 mt-1">Cliques no vídeo</p>
                    </div>
                    
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">CTR</span>
                            <i class="fas fa-percentage text-gray-400"></i>
                        </div>
                        <p class="text-3xl font-bold text-gray-800" id="metricsCTR">0%</p>
                        <p class="text-xs text-gray-500 mt-1">Taxa de cliques</p>
                    </div>
                    
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">CPC</span>
                            <i class="fas fa-coins text-gray-400"></i>
                        </div>
                        <p class="text-3xl font-bold text-gray-800" id="metricsCPC">R$ 0,00</p>
                        <p class="text-xs text-gray-500 mt-1">Custo por clique</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-3">
                <button type="button" id="toggleBoostBtn" onclick="toggleBoostStatus()" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-pause mr-2"></i>Pausar Campanha
                </button>
                <button type="button" id="finalizeBoostBtn" onclick="finalizeBoost()" class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-stop-circle mr-2"></i>Finalizar e Reembolsar
                </button>
                <button type="button" onclick="closeBoostMetricsModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-times mr-2"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Form submission handler
const uploadForm = document.getElementById('uploadVideoForm');
if (uploadForm) {
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevenir envio normal do formulário
        
        const videoFile = document.getElementById('videoFile').files[0];
        
        if (!videoFile) {
            alert('Por favor, selecione um vídeo!');
            return false;
        }
        
        // Validação de tamanho (20MB máximo para servidor de desenvolvimento)
        const maxSize = 20 * 1024 * 1024; // 20MB
        if (videoFile.size > maxSize) {
            alert('O vídeo não pode ser maior que 20MB.\n\n⚠️ LIMITE TEMPORÁRIO: Este limite existe porque você está usando o servidor de desenvolvimento do Laravel (php artisan serve).\n\nPara fazer upload de vídeos maiores (até 500MB), você precisa:\n• Instalar Laravel Valet (recomendado para Mac)\n• Configurar Apache ou Nginx\n• Ver detalhes em UPLOAD_SOLUTION.md');
            return false;
        }
        
        console.log('Preparando upload do vídeo:', {
            name: videoFile.name,
            size: videoFile.size,
            type: videoFile.type,
            maxAllowed: maxSize
        });
        
        // Mostrar loading
        const submitBtn = document.getElementById('submitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitBtnLoading = document.getElementById('submitBtnLoading');
        
        submitBtn.disabled = true;
        submitBtnText.classList.add('hidden');
        submitBtnLoading.classList.remove('hidden');
        
        // Criar FormData com todos os campos do formulário
        const formData = new FormData();
        
        // Adicionar CSRF token
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        
        // Adicionar campos do formulário
        formData.append('title', document.querySelector('input[name="title"]').value);
        formData.append('description', document.querySelector('textarea[name="description"]').value || '');
        formData.append('place_id', document.querySelector('select[name="place_id"]').value || '');
        
        // Adicionar status
        const statusRadio = document.querySelector('input[name="status"]:checked');
        if (statusRadio) {
            formData.append('status', statusRadio.value);
        }
        
        // Adicionar vídeo
        formData.append('video', videoFile);
        
        // Adicionar thumbnail se existir
        const thumbnailFile = document.getElementById('thumbnailFile').files[0];
        if (thumbnailFile) {
            formData.append('thumbnail', thumbnailFile);
        }
        
        console.log('FormData criado. Iniciando upload...');
        console.log('Campos no FormData:');
        for (let pair of formData.entries()) {
            if (pair[1] instanceof File) {
                console.log(pair[0] + ': [File] ' + pair[1].name + ' (' + pair[1].size + ' bytes, type: ' + pair[1].type + ')');
            } else {
                console.log(pair[0] + ': ' + pair[1]);
            }
        }
        
        // Fazer upload via AJAX
        fetch(uploadForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Resposta recebida:', response.status);
            
            if (!response.ok) {
                // Tentar pegar o erro como JSON
                return response.json().then(errorData => {
                    console.error('Erro do servidor:', errorData);
                    throw new Error(errorData.message || 'Erro ao fazer upload');
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Upload concluído:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Erro desconhecido');
            }
            
            // Resetar botão
            submitBtn.disabled = false;
            submitBtnText.classList.remove('hidden');
            submitBtnLoading.classList.add('hidden');
            
            // Fechar modal
            closeUploadModal();
            
            // Mostrar mensagem de sucesso
            alert('Vídeo enviado com sucesso!');
            
            // Recarregar a página para mostrar o novo vídeo
            window.location.reload();
        })
        .catch(error => {
            console.error('Erro no upload:', error);
            
            // Resetar botão
            submitBtn.disabled = false;
            submitBtnText.classList.remove('hidden');
            submitBtnLoading.classList.add('hidden');
            
            alert('Erro ao enviar vídeo: ' + error.message);
        });
        
        return false;
    });
}

function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
    document.getElementById('uploadModal').classList.add('flex');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadModal').classList.remove('flex');
    
    // Reset do formulário
    const form = document.getElementById('uploadVideoForm');
    if (form) form.reset();
    
    // Reset do botão
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const submitBtnLoading = document.getElementById('submitBtnLoading');
    
    if (submitBtn) submitBtn.disabled = false;
    if (submitBtnText) submitBtnText.classList.remove('hidden');
    if (submitBtnLoading) submitBtnLoading.classList.add('hidden');
    
    clearVideoPreview();
    clearThumbnailPreview();
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

// Video Preview
const videoFileInput = document.getElementById('videoFile');
if (videoFileInput) {
    videoFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        console.log('Arquivo selecionado:', file);
        
        if (file) {
            // Validar tamanho (500MB = 524288000 bytes)
            if (file.size > 524288000) {
                alert('O arquivo é muito grande! Tamanho máximo: 500MB');
                e.target.value = '';
                return;
            }
            
            // Validar tipo
            const validTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'];
            if (!validTypes.includes(file.type)) {
                alert('Formato inválido! Use: MP4, MOV, AVI ou WMV');
                e.target.value = '';
                return;
            }
            
            console.log('Validação passou, mostrando preview');
            
            // Mostrar preview
            const videoPreview = document.getElementById('videoPreview');
            const videoPreviewContainer = document.getElementById('videoPreviewContainer');
            const videoDropArea = document.getElementById('videoDropArea');
            const videoFileName = document.getElementById('videoFileName');
            
            const url = URL.createObjectURL(file);
            videoPreview.src = url;
            videoFileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
            
            videoPreviewContainer.classList.remove('hidden');
            videoDropArea.classList.add('hidden');
            
            console.log('Preview configurado. Arquivo mantido no input:', videoFileInput.files[0]);
        }
    });
}

function clearVideoPreview() {
    const videoFileInput = document.getElementById('videoFile');
    const videoPreview = document.getElementById('videoPreview');
    const videoPreviewContainer = document.getElementById('videoPreviewContainer');
    const videoDropArea = document.getElementById('videoDropArea');
    
    videoFileInput.value = '';
    videoPreview.src = '';
    videoPreviewContainer.classList.add('hidden');
    videoDropArea.classList.remove('hidden');
}

// Thumbnail Preview
const thumbnailFileInput = document.getElementById('thumbnailFile');
if (thumbnailFileInput) {
    thumbnailFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validar tamanho (5MB = 5242880 bytes)
            if (file.size > 5242880) {
                alert('A imagem é muito grande! Tamanho máximo: 5MB');
                e.target.value = '';
                return;
            }
            
            // Validar tipo
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!validTypes.includes(file.type)) {
                alert('Formato inválido! Use: JPG ou PNG');
                e.target.value = '';
                return;
            }
            
            // Mostrar preview
            const thumbnailPreview = document.getElementById('thumbnailPreview');
            const thumbnailPreviewContainer = document.getElementById('thumbnailPreviewContainer');
            
            const reader = new FileReader();
            reader.onload = function(e) {
                thumbnailPreview.src = e.target.result;
                thumbnailPreviewContainer.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
}

function clearThumbnailPreview() {
    const thumbnailFileInput = document.getElementById('thumbnailFile');
    const thumbnailPreview = document.getElementById('thumbnailPreview');
    const thumbnailPreviewContainer = document.getElementById('thumbnailPreviewContainer');
    
    thumbnailFileInput.value = '';
    thumbnailPreview.src = '';
    thumbnailPreviewContainer.classList.add('hidden');
}

// Helper function to format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Drag and Drop for video
const videoDropArea = document.getElementById('videoDropArea');
if (videoDropArea) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        videoDropArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        videoDropArea.addEventListener(eventName, () => {
            videoDropArea.classList.add('border-purple-500', 'bg-purple-50');
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        videoDropArea.addEventListener(eventName, () => {
            videoDropArea.classList.remove('border-purple-500', 'bg-purple-50');
        }, false);
    });
    
    videoDropArea.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            videoFileInput.files = files;
            videoFileInput.dispatchEvent(new Event('change'));
        }
    }, false);
}

// Video Player Functions
function openVideoPlayer(videoUrl, videoTitle) {
    const modal = document.getElementById('videoPlayerModal');
    const player = document.getElementById('videoPlayer');
    const source = document.getElementById('videoSource');
    const title = document.getElementById('videoPlayerTitle');
    
    source.src = videoUrl;
    title.textContent = videoTitle;
    player.load();
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Auto play
    player.play();
}

function closeVideoPlayer() {
    const modal = document.getElementById('videoPlayerModal');
    const player = document.getElementById('videoPlayer');
    
    player.pause();
    player.currentTime = 0;
    
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Boost Metrics Modal Functions
let currentBoostId = null;
let currentBoostStatus = null;

function openBoostMetricsModal(videoId, videoTitle, boostData) {
    const modal = document.getElementById('boostMetricsModal');
    
    // Armazenar ID do boost para uso posterior
    currentBoostId = boostData.id;
    currentBoostStatus = boostData.status;
    
    // Preencher informações do vídeo
    document.getElementById('metricsVideoTitle').textContent = videoTitle;
    
    // Atualizar status
    const statusElement = document.getElementById('metricsStatus');
    if (boostData.status === 'active') {
        statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Ativo';
        statusElement.className = 'ml-2 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium';
    } else if (boostData.status === 'paused') {
        statusElement.innerHTML = '<i class="fas fa-pause-circle"></i> Pausado';
        statusElement.className = 'ml-2 px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-medium';
    } else {
        statusElement.innerHTML = '<i class="fas fa-times-circle"></i> Encerrado';
        statusElement.className = 'ml-2 px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium';
    }
    
    // Atualizar botão de pausar/retomar
    const toggleBtn = document.getElementById('toggleBoostBtn');
    const finalizeBtn = document.getElementById('finalizeBoostBtn');
    
    if (boostData.status === 'active') {
        toggleBtn.innerHTML = '<i class="fas fa-pause mr-2"></i>Pausar Campanha';
        toggleBtn.className = 'flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-lg font-semibold transition';
        toggleBtn.style.display = 'block';
        finalizeBtn.style.display = 'block';
    } else if (boostData.status === 'paused') {
        toggleBtn.innerHTML = '<i class="fas fa-play mr-2"></i>Retomar Campanha';
        toggleBtn.className = 'flex-1 bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-semibold transition';
        toggleBtn.style.display = 'block';
        finalizeBtn.style.display = 'block';
    } else {
        toggleBtn.style.display = 'none';
        finalizeBtn.style.display = 'none';
    }
    
    // Preencher dados do boost
    const amount = parseFloat(boostData.amount);
    const dailyBudget = parseFloat(boostData.daily_budget);
    const spent = parseFloat(boostData.spent || 0);
    const impressions = parseInt(boostData.impressions || 0);
    const clicks = parseInt(boostData.clicks || 0);
    
    document.getElementById('metricsTotalAmount').textContent = 'R$ ' + amount.toFixed(2).replace('.', ',');
    document.getElementById('metricsDailyBudget').textContent = 'R$ ' + dailyBudget.toFixed(2).replace('.', ',');
    document.getElementById('metricsSpent').textContent = 'R$ ' + spent.toFixed(2).replace('.', ',');
    document.getElementById('metricsImpressions').textContent = impressions.toLocaleString('pt-BR');
    document.getElementById('metricsClicks').textContent = clicks.toLocaleString('pt-BR');
    
    // Calcular CTR e CPC
    const ctr = impressions > 0 ? (clicks / impressions * 100) : 0;
    const cpc = clicks > 0 ? (spent / clicks) : 0;
    
    document.getElementById('metricsCTR').textContent = ctr.toFixed(2) + '%';
    document.getElementById('metricsCPC').textContent = 'R$ ' + cpc.toFixed(2).replace('.', ',');
    
    // Período
    const startDate = new Date(boostData.start_date);
    const endDate = new Date(boostData.end_date);
    document.getElementById('metricsPeriod').textContent = 
        startDate.toLocaleDateString('pt-BR') + ' - ' + endDate.toLocaleDateString('pt-BR');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeBoostMetricsModal() {
    const modal = document.getElementById('boostMetricsModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function pauseBoost() {
    if (!currentBoostId) {
        alert('Erro: ID do boost não encontrado');
        return;
    }

    const action = currentBoostStatus === 'active' ? 'pausar' : 'retomar';
    const confirmMessage = currentBoostStatus === 'active' 
        ? 'Tem certeza que deseja pausar esta campanha?\n\nA campanha não gastará mais budget até ser retomada.' 
        : 'Tem certeza que deseja retomar esta campanha?\n\nA campanha voltará a gastar o budget diário.';
    
    if (!confirm(confirmMessage)) {
        return;
    }

    // Mostrar loading no botão
    const toggleBtn = document.getElementById('toggleBoostBtn');
    const originalContent = toggleBtn.innerHTML;
    toggleBtn.disabled = true;
    toggleBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processando...';

    // Fazer requisição AJAX
    fetch(`/influencer/videos/boost/${currentBoostId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Atualizar status atual
            currentBoostStatus = data.status;
            
            // Atualizar interface
            const statusElement = document.getElementById('metricsStatus');
            if (data.status === 'active') {
                statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Ativo';
                statusElement.className = 'ml-2 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium';
                toggleBtn.innerHTML = '<i class="fas fa-pause mr-2"></i>Pausar Campanha';
                toggleBtn.className = 'flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-lg font-semibold transition';
            } else {
                statusElement.innerHTML = '<i class="fas fa-pause-circle"></i> Pausado';
                statusElement.className = 'ml-2 px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-medium';
                toggleBtn.innerHTML = '<i class="fas fa-play mr-2"></i>Retomar Campanha';
                toggleBtn.className = 'flex-1 bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-semibold transition';
            }
            
            toggleBtn.disabled = false;
            
            // Recarregar a página após 1 segundo para atualizar a lista de vídeos
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Erro: ' + data.message);
            toggleBtn.disabled = false;
            toggleBtn.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar requisição. Tente novamente.');
        toggleBtn.disabled = false;
        toggleBtn.innerHTML = originalContent;
    });
}

function finalizeBoost() {
    if (!currentBoostId) {
        alert('Erro: ID do boost não encontrado');
        return;
    }

    const confirmMessage = 'ATENÇÃO: Esta ação é IRREVERSÍVEL!\n\n' +
        'Ao finalizar a campanha:\n' +
        '✓ O saldo não gasto será devolvido à sua carteira\n' +
        '✓ A campanha será marcada como concluída\n' +
        '✗ NÃO será possível retomar esta campanha\n\n' +
        'Tem certeza que deseja finalizar?';
    
    if (!confirm(confirmMessage)) {
        return;
    }

    // Mostrar loading no botão
    const finalizeBtn = document.getElementById('finalizeBoostBtn');
    const originalContent = finalizeBtn.innerHTML;
    finalizeBtn.disabled = true;
    finalizeBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processando...';

    // Fazer requisição AJAX
    fetch(`/influencer/videos/boost/${currentBoostId}/finalize`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const refundedAmount = parseFloat(data.refunded || 0);
            const message = data.message + 
                (refundedAmount > 0 
                    ? `\n\nValor reembolsado: R$ ${refundedAmount.toFixed(2).replace('.', ',')}` 
                    : '\n\nTodo o orçamento já foi utilizado.');
            
            alert(message);
            
            // Fechar modal e recarregar página
            closeBoostMetricsModal();
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Erro: ' + data.message);
            finalizeBtn.disabled = false;
            finalizeBtn.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao finalizar campanha. Por favor, tente novamente.');
        finalizeBtn.disabled = false;
        finalizeBtn.innerHTML = originalContent;
    });
}

function toggleBoostStatus() {
    pauseBoost();
}
</script>
@endpush
