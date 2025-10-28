@extends('layouts.admin')

@section('title', 'Editar Banner')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.banners') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i>Voltar
    </a>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Banner</h2>
    
    <form method="POST" action="{{ route('admin.banners.update', $banner->id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">Título</label>
            <input 
                type="text" 
                name="title" 
                value="{{ old('title', $banner->title) }}"
                required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
            @error('title')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                <i class="fas fa-image mr-1"></i>Nova Imagem (opcional)
            </label>
            <input 
                type="file" 
                name="image" 
                accept="image/*"
                onchange="previewBannerImage(event)"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
            @error('image')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">
                <i class="fas fa-info-circle"></i> Deixe em branco para manter a imagem atual
            </p>
            
            @if($banner->image_url)
            <div class="mt-3">
                <p class="text-sm text-gray-600 mb-2">Imagem Atual:</p>
                <img id="currentImg" src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full max-w-2xl rounded-lg border-2 border-gray-200">
            </div>
            @endif
            
            <div id="bannerPreview" class="mt-3 hidden">
                <p class="text-sm text-gray-600 mb-2">Nova Imagem:</p>
                <img id="previewImg" src="" alt="Preview" class="w-full max-w-2xl rounded-lg border-2 border-green-500">
            </div>
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">Link (opcional)</label>
            <input 
                type="url" 
                name="link" 
                value="{{ old('link', $banner->link) }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
            @error('link')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="flex items-center">
            <input 
                type="checkbox" 
                name="is_active" 
                id="is_active"
                {{ old('is_active', $banner->is_active) ? 'checked' : '' }}
                value="1"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2"
            >
            <label for="is_active" class="text-gray-700 text-sm font-semibold">Banner Ativo</label>
        </div>
        
        <div class="flex gap-4 pt-4">
            <button 
                type="submit" 
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                <i class="fas fa-save mr-2"></i>Atualizar Banner
            </button>
            <a 
                href="{{ route('admin.banners') }}" 
                class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
function previewBannerImage(event) {
    const preview = document.getElementById('previewImg');
    const previewDiv = document.getElementById('bannerPreview');
    const currentImg = document.getElementById('currentImg');
    const file = event.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewDiv.classList.remove('hidden');
            if (currentImg) {
                currentImg.style.opacity = '0.5';
            }
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
