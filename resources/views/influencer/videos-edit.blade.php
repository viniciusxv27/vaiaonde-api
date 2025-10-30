@extends('layouts.influencer')

@section('title', 'Editar Vídeo')
@section('page-title', 'Editar Vídeo')
@section('page-subtitle', 'Atualize as informações do seu vídeo')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Video Preview Card -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-video text-purple-600 mr-2"></i>Vídeo Atual
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Video Player -->
            <div>
                <label class="block text-sm font-medium mb-2">Vídeo</label>
                <video controls class="w-full rounded-lg shadow-lg">
                    <source src="{{ $video->video_url }}" type="video/mp4">
                    Seu navegador não suporta a tag de vídeo.
                </video>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Você pode substituir este vídeo enviando um novo arquivo abaixo
                </p>
            </div>
            
            <!-- Thumbnail -->
            <div>
                <label class="block text-sm font-medium mb-2">Miniatura Atual</label>
                @if($video->thumbnail_url)
                <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="w-full rounded-lg shadow-lg">
                @else
                <div class="w-full aspect-video bg-gray-200 rounded-lg flex items-center justify-center">
                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                </div>
                @endif
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Você pode atualizar a miniatura enviando uma nova imagem abaixo
                </p>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form method="POST" action="{{ route('influencer.videos.update', $video->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Title -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Título do Vídeo *</label>
                <input type="text" name="title" value="{{ old('title', $video->title) }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('title') border-red-500 @enderror" 
                    placeholder="Ex: Conhecendo o melhor restaurante de São Paulo" required>
                @error('title')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Description -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Descrição</label>
                <textarea name="description" rows="4" 
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('description') border-red-500 @enderror" 
                    placeholder="Conte mais sobre este vídeo...">{{ old('description', $video->description) }}</textarea>
                @error('description')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Place -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Estabelecimento</label>
                <select name="place_id" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">Nenhum (opcional)</option>
                    @foreach($places ?? [] as $place)
                    <option value="{{ $place->id }}" {{ old('place_id', $video->place_id) == $place->id ? 'selected' : '' }}>
                        {{ $place->name }}
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Vincule este vídeo a um estabelecimento</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Replace Video -->
                <div>
                    <label class="block text-sm font-medium mb-2">Substituir Vídeo (Opcional)</label>
                    <input type="file" name="video" id="videoFile" accept="video/*" class="hidden">
                    <label for="videoFile" class="block border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-500 transition cursor-pointer">
                        <i class="fas fa-video text-2xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">Clique para selecionar novo vídeo</p>
                        <p class="text-xs text-gray-500 mt-1">MP4, MOV, AVI - Máx. 500MB</p>
                    </label>
                    <!-- Video Preview -->
                    <div id="videoPreviewContainer" class="hidden mt-4">
                        <label class="block text-sm font-medium mb-2">Novo Vídeo</label>
                        <video id="videoPreview" controls class="w-full rounded-lg shadow-lg max-h-64"></video>
                        <div class="mt-2 flex items-center justify-between text-sm">
                            <span id="videoFileName" class="text-gray-600"></span>
                            <button type="button" onclick="clearVideoPreview()" class="text-red-600 hover:text-red-700">
                                <i class="fas fa-times mr-1"></i>Remover
                            </button>
                        </div>
                    </div>
                    @error('video')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Replace Thumbnail -->
                <div>
                    <label class="block text-sm font-medium mb-2">Nova Miniatura (Opcional)</label>
                    <input type="file" name="thumbnail" id="thumbnailFile" accept="image/*" class="hidden">
                    <label for="thumbnailFile" class="block border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-500 transition cursor-pointer">
                        <i class="fas fa-image text-2xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">Clique para selecionar nova miniatura</p>
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG - Recomendado 1280x720px</p>
                    </label>
                    <!-- Thumbnail Preview -->
                    <div id="thumbnailPreviewContainer" class="hidden mt-4">
                        <label class="block text-sm font-medium mb-2">Nova Thumbnail</label>
                        <div class="relative inline-block">
                            <img id="thumbnailPreview" class="rounded-lg shadow-lg max-h-48">
                            <button type="button" onclick="clearThumbnailPreview()" class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @error('thumbnail')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Status -->
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Status do Vídeo</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="status" value="published" class="mr-2" {{ old('status', $video->active ? 'published' : 'draft') == 'published' ? 'checked' : '' }}>
                        <span class="text-sm">
                            <i class="fas fa-globe text-green-600 mr-2"></i>
                            <strong>Publicado</strong> - O vídeo está visível para todos
                        </span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="status" value="draft" class="mr-2" {{ old('status', $video->active ? 'published' : 'draft') == 'draft' ? 'checked' : '' }}>
                        <span class="text-sm">
                            <i class="fas fa-file text-gray-600 mr-2"></i>
                            <strong>Rascunho</strong> - O vídeo não está visível
                        </span>
                    </label>
                </div>
            </div>
            
            <!-- Stats Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-blue-900 mb-2">
                    <i class="fas fa-chart-bar mr-2"></i>Estatísticas do Vídeo
                </h4>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-blue-600 font-medium">Visualizações</p>
                        <p class="text-2xl font-bold text-blue-900">{{ number_format($video->views_count ?? 0) }}</p>
                    </div>
                    <div>
                        <p class="text-blue-600 font-medium">Curtidas</p>
                        <p class="text-2xl font-bold text-blue-900">{{ number_format($video->likes_count ?? 0) }}</p>
                    </div>
                    <div>
                        <p class="text-blue-600 font-medium">Compartilhamentos</p>
                        <p class="text-2xl font-bold text-blue-900">{{ number_format($video->shares_count ?? 0) }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <a href="{{ route('influencer.videos.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
                <div class="flex space-x-3">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-save mr-2"></i>Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Video Preview
const videoFileInput = document.getElementById('videoFile');
if (videoFileInput) {
    videoFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
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
            
            // Mostrar preview
            const videoPreview = document.getElementById('videoPreview');
            const videoPreviewContainer = document.getElementById('videoPreviewContainer');
            const videoFileName = document.getElementById('videoFileName');
            
            const url = URL.createObjectURL(file);
            videoPreview.src = url;
            videoFileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
            
            videoPreviewContainer.classList.remove('hidden');
        }
    });
}

function clearVideoPreview() {
    const videoFileInput = document.getElementById('videoFile');
    const videoPreview = document.getElementById('videoPreview');
    const videoPreviewContainer = document.getElementById('videoPreviewContainer');
    
    videoFileInput.value = '';
    videoPreview.src = '';
    videoPreviewContainer.classList.add('hidden');
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
</script>
@endpush
