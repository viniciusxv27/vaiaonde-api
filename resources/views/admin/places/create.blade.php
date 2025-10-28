@extends('layouts.admin')

@section('title', 'Criar Lugar')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.places') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i>Voltar
    </a>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Criar Novo Lugar</h2>
    
    <form method="POST" action="{{ route('admin.places.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nome do Lugar</label>
                <input 
                    type="text" 
                    name="name" 
                    value="{{ old('name') }}"
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Tipo</label>
                <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="lugar" {{ old('type') == 'lugar' ? 'selected' : '' }}>Lugar</option>
                    <option value="restaurante" {{ old('type') == 'restaurante' ? 'selected' : '' }}>Restaurante</option>
                    <option value="evento" {{ old('type') == 'evento' ? 'selected' : '' }}>Evento</option>
                </select>
                @error('type')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Proprietário</label>
                <select name="owner_id" id="owner_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Nenhum (será ativado sem assinatura)</option>
                    @foreach(\App\Models\User::where('role', 'proprietario')->get() as $owner)
                    <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                        {{ $owner->name }} ({{ $owner->email }})
                    </option>
                    @endforeach
                </select>
                @error('owner_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div id="planSelector" style="display: none;">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Plano de Assinatura</label>
                <select name="plan_id" id="plan_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione um plano...</option>
                    @foreach(\App\Models\PartnerSubscriptionPlan::where('is_active', true)->get() as $plan)
                    <option value="{{ $plan->id }}" data-price="{{ $plan->price }}">
                        {{ $plan->name }} - R$ {{ number_format($plan->price, 2, ',', '.') }}/mês
                    </option>
                    @endforeach
                </select>
                <p class="text-sm text-gray-600 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Uma assinatura será criada automaticamente ao selecionar um plano
                </p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Cidade</label>
                <select name="city_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione...</option>
                    @foreach(\App\Models\City::all() as $city)
                    <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                    @endforeach
                </select>
                @error('city_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Categoria</label>
                <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Nenhuma</option>
                    @foreach(\App\Models\Categorie::all() as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Telefone</label>
                <input 
                    type="text" 
                    name="phone" 
                    value="{{ old('phone') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Imagem do Lugar</label>
                <input 
                    type="file" 
                    name="image" 
                    accept="image/*"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    onchange="previewImage(event)"
                >
                <div id="imagePreview" class="mt-2 hidden">
                    <img id="preview" class="w-32 h-32 object-cover rounded-lg">
                </div>
            </div>
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">Descrição</label>
            <textarea 
                name="description" 
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >{{ old('description') }}</textarea>
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">Endereço</label>
            <input 
                type="text" 
                id="searchAddress"
                placeholder="Digite o endereço e pressione Enter para buscar no mapa"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-2"
            >
            <input 
                type="text" 
                name="address" 
                id="address"
                value="{{ old('address') }}"
                placeholder="Endereço completo será preenchido automaticamente"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                readonly
            >
        </div>
        
        <!-- Mapa Interativo -->
        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">Localização no Mapa</label>
            <div id="map" class="w-full h-96 rounded-lg border border-gray-300"></div>
            <p class="text-sm text-gray-600 mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                Clique no mapa ou busque um endereço para definir a localização
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Latitude</label>
                <input 
                    type="number" 
                    name="latitude" 
                    id="latitude"
                    value="{{ old('latitude') }}"
                    step="any"
                    readonly
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                >
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Longitude</label>
                <input 
                    type="number" 
                    name="longitude" 
                    id="longitude"
                    value="{{ old('longitude') }}"
                    step="any"
                    readonly
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                >
            </div>
        </div>
        
        <div class="flex gap-4 pt-4">
            <button 
                type="submit" 
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                <i class="fas fa-save mr-2"></i>Criar Lugar
            </button>
            <a 
                href="{{ route('admin.places') }}" 
                class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Preview de imagem
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

// Mapa Interativo
let map, marker;

// Inicializa o mapa centrado no Brasil
map = L.map('map').setView([-23.550520, -46.633308], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Adiciona marcador ao clicar no mapa
map.on('click', function(e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;
    
    setMarker(lat, lng);
    reverseGeocode(lat, lng);
});

// Função para adicionar/mover marcador
function setMarker(lat, lng) {
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng]).addTo(map);
    }
    
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
}

// Geocodificação reversa (coordenadas -> endereço)
function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        .then(response => response.json())
        .then(data => {
            if (data.display_name) {
                document.getElementById('address').value = data.display_name;
            }
        });
}

// Busca de endereço
document.getElementById('searchAddress').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const address = this.value;
        
        if (address) {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        
                        map.setView([lat, lng], 16);
                        setMarker(lat, lng);
                        document.getElementById('address').value = result.display_name;
                    } else {
                        alert('Endereço não encontrado. Tente novamente.');
                    }
                });
        }
    });
});
</script>

<script>
// Mostrar seletor de plano quando proprietário for selecionado
document.getElementById('owner_id').addEventListener('change', function() {
    const planSelector = document.getElementById('planSelector');
    if (this.value) {
        planSelector.style.display = 'block';
    } else {
        planSelector.style.display = 'none';
        document.getElementById('plan_id').value = '';
    }
});

// Trigger no carregamento se já tiver proprietário selecionado
if (document.getElementById('owner_id').value) {
    document.getElementById('planSelector').style.display = 'block';
}
</script>
@endsection