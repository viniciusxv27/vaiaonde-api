@extends('layouts.partner')

@section('title', 'Editar Estabelecimento')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center mb-6">
        <a href="{{ route('partner.places') }}" class="text-blue-600 hover:text-blue-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Editar Estabelecimento</h1>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('partner.places.update', $place->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nome -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome do Estabelecimento *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $place->name) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" id="type" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('type') border-red-500 @enderror" required>
                        <option value="">Selecione o tipo</option>
                        <option value="lugar" {{ old('type', $place->type) == 'lugar' ? 'selected' : '' }}>Lugar</option>
                        <option value="restaurante" {{ old('type', $place->type) == 'restaurante' ? 'selected' : '' }}>Restaurante</option>
                        <option value="evento" {{ old('type', $place->type) == 'evento' ? 'selected' : '' }}>Evento</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cidade -->
                <div>
                    <label for="city_id" class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
                    <select name="city_id" id="city_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('city_id') border-red-500 @enderror" required>
                        <option value="">Selecione a cidade</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('city_id', $place->city_id) == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('city_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Categoria -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <select name="category_id" id="category_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecione a categoria</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $place->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Imagem -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Imagem do Estabelecimento</label>
                    <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(event)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('image') border-red-500 @enderror">
                    @error('image')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @if($place->image)
                        <div class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">Imagem atual:</p>
                            <img src="{{ asset($place->image) }}" alt="{{ $place->name }}" class="w-full h-48 object-cover rounded-lg">
                        </div>
                    @endif
                    <div id="imagePreview" class="mt-2 hidden">
                        <p class="text-sm text-gray-600 mb-1">Nova imagem:</p>
                        <img id="preview" src="" alt="Preview" class="w-full h-48 object-cover rounded-lg">
                    </div>
                </div>

                <!-- Descrição -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="description" id="description" rows="4" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $place->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Endereço -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                    <div class="flex gap-2">
                        <input type="text" name="address" id="address" value="{{ old('address', $place->address) }}" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-500 @enderror">
                        <button type="button" onclick="searchAddress()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mapa -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Localização no Mapa</label>
                    <div id="map" style="height: 400px;" class="rounded-lg border border-gray-300"></div>
                    <p class="text-xs text-gray-500 mt-1">Clique no mapa para definir a localização do seu estabelecimento</p>
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $place->coords->latitude ?? '') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $place->coords->longitude ?? '') }}">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('partner.places') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold transition">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-save mr-2"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Image preview
    function previewImage(event) {
        const preview = document.getElementById('preview');
        const previewDiv = document.getElementById('imagePreview');
        const file = event.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewDiv.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    // Initialize map
    const existingLat = {{ $place->coords->latitude ?? -15.7942 }};
    const existingLng = {{ $place->coords->longitude ?? -47.8822 }};
    const hasCoords = {{ $place->coords ? 'true' : 'false' }};
    
    const map = L.map('map').setView([existingLat, existingLng], hasCoords ? 15 : 4);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker;
    
    // Add existing marker if coordinates exist
    if (hasCoords) {
        marker = L.marker([existingLat, existingLng]).addTo(map);
    }

    // Click on map to set location
    map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        
        if (marker) {
            map.removeLayer(marker);
        }
        
        marker = L.marker([lat, lng]).addTo(map);
        
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        
        // Reverse geocoding
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data.display_name) {
                    document.getElementById('address').value = data.display_name;
                }
            });
    });

    // Search address
    function searchAddress() {
        const address = document.getElementById('address').value;
        
        if (!address) {
            alert('Digite um endereço para buscar');
            return;
        }
        
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const { lat, lon } = data[0];
                    
                    map.setView([lat, lon], 15);
                    
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    
                    marker = L.marker([lat, lon]).addTo(map);
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lon;
                } else {
                    alert('Endereço não encontrado');
                }
            });
    }
</script>
@endsection
