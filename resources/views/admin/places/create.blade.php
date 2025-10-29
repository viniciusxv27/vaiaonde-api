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
                <select name="type" id="type" onchange="loadCategories()" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione o tipo</option>
                    @foreach($tipes as $tipe)
                        <option value="{{ strtolower($tipe->name) }}" {{ old('type') == strtolower($tipe->name) ? 'selected' : '' }}>
                            {{ ucfirst($tipe->name) }}
                        </option>
                    @endforeach
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
                <label class="block text-gray-700 text-sm font-semibold mb-2">Categorias</label>
                <div id="categoriesContainer" class="grid grid-cols-2 gap-3">
                    <p class="text-gray-500 text-sm col-span-full">Selecione um tipo primeiro para ver as categorias disponíveis</p>
                </div>
                @error('categories')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
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
                <label class="block text-gray-700 text-sm font-semibold mb-2">Instagram</label>
                <input 
                    type="url" 
                    name="instagram_url" 
                    value="{{ old('instagram_url') }}"
                    placeholder="https://instagram.com/..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Link Google Maps</label>
                <input 
                    type="url" 
                    name="location_url" 
                    value="{{ old('location_url') }}"
                    placeholder="https://maps.google.com/..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Link Uber</label>
                <input 
                    type="url" 
                    name="uber_url" 
                    value="{{ old('uber_url') }}"
                    placeholder="https://uber.com/..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
            </div>
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">Localização (Texto)</label>
            <input 
                type="text" 
                name="location" 
                value="{{ old('location') }}"
                placeholder="Ex: Centro, Vitória - ES"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
        </div>
        
        <!-- Logo -->
        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">Logo do Lugar *</label>
            <input 
                type="file" 
                name="logo" 
                accept="image/*"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                onchange="previewLogo(event)"
            >
            <p class="text-sm text-gray-600 mt-1">
                <i class="fas fa-info-circle mr-1"></i>
                Logo/ícone do estabelecimento (será exibido em miniatura)
            </p>
            <div id="logoPreview" class="mt-4"></div>
        </div>
        
        <!-- Descrição -->
        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">Descrição do Lugar *</label>
            <textarea 
                name="review" 
                required
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                placeholder="Descreva o lugar, suas características, diferenciais..."
            >{{ old('review') }}</textarea>
            @error('review')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Múltiplas Imagens -->
        <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Imagens do Lugar *</label>
            <input 
                type="file" 
                name="images[]" 
                accept="image/*"
                multiple
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                onchange="previewMultipleImages(event)"
            >
            <input type="hidden" name="card_image_index" id="card_image_index" value="0">
            <p class="text-sm text-gray-600 mt-1">
                <i class="fas fa-info-circle mr-1"></i>
                Selecione múltiplas imagens (máx. 5MB cada). Clique em uma imagem para defini-la como principal (imagem do card).
            </p>
            @error('images')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            @error('images.*')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <div id="imagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
        </div>
        
        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">Buscar Endereço</label>
            <input 
                type="text" 
                id="searchAddress"
                placeholder="Digite o endereço e pressione Enter para buscar no mapa"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
            <p class="text-sm text-gray-600 mt-1">
                <i class="fas fa-info-circle mr-1"></i>
                Digite o endereço e pressione Enter ou clique no mapa para definir a localização
            </p>
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
// Salvar e restaurar dados do formulário
const FORM_DATA_KEY = 'admin_place_form_data';

// Restaurar dados ao carregar a página
window.addEventListener('DOMContentLoaded', function() {
    const savedData = localStorage.getItem(FORM_DATA_KEY);
    if (savedData) {
        const data = JSON.parse(savedData);
        
        // Restaurar campos de texto
        if (data.latitude) {
            document.getElementById('latitude').value = data.latitude;
            document.getElementById('longitude').value = data.longitude;
            
            // Restaurar marcador no mapa
            if (map && data.latitude && data.longitude) {
                const lat = parseFloat(data.latitude);
                const lng = parseFloat(data.longitude);
                setTimeout(() => {
                    map.setView([lat, lng], 16);
                    setMarker(lat, lng);
                }, 500);
            }
        }
        
        if (data.location) document.querySelector('input[name="location"]').value = data.location;
        if (data.uber_url) document.querySelector('input[name="uber_url"]').value = data.uber_url;
        if (data.location_url) document.querySelector('input[name="location_url"]').value = data.location_url;
    }
});

// Salvar dados do formulário antes de submeter
document.querySelector('form').addEventListener('submit', function() {
    // Limpar dados salvos após submit bem-sucedido
    localStorage.removeItem(FORM_DATA_KEY);
});

// Salvar coordenadas e localização quando mudarem
function saveFormData() {
    const data = {
        latitude: document.getElementById('latitude').value,
        longitude: document.getElementById('longitude').value,
        location: document.querySelector('input[name="location"]').value,
        uber_url: document.querySelector('input[name="uber_url"]').value,
        location_url: document.querySelector('input[name="location_url"]').value,
    };
    localStorage.setItem(FORM_DATA_KEY, JSON.stringify(data));
}

// Preview de múltiplas imagens com seleção da principal
let selectedCardIndex = 0;

function previewMultipleImages(event) {
    const previewContainer = document.getElementById('imagePreview');
    const files = event.target.files;
    const maxFileSize = 5 * 1024 * 1024; // 5MB em bytes
    
    previewContainer.innerHTML = '';
    selectedCardIndex = 0;
    document.getElementById('card_image_index').value = 0;
    
    // Validar tamanho dos arquivos
    let hasOversizedFile = false;
    let oversizedFiles = [];
    
    Array.from(files).forEach((file) => {
        if (file.size > maxFileSize) {
            hasOversizedFile = true;
            oversizedFiles.push(`${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`);
        }
    });
    
    if (hasOversizedFile) {
        alert(`Os seguintes arquivos excedem o tamanho máximo de 5MB:\n\n${oversizedFiles.join('\n')}\n\nPor favor, selecione imagens menores.`);
        event.target.value = ''; // Limpa o input
        return;
    }
    
    if (files.length > 0) {
        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative cursor-pointer hover:opacity-90 transition';
                div.setAttribute('data-index', index);
                div.onclick = function() { selectCardImage(index); };
                
                const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg border-4 image-border-${index} ${index === 0 ? 'border-[#FEB800]' : 'border-gray-300'}">
                    <span class="card-badge-${index} ${index === 0 ? '' : 'hidden'} absolute top-2 right-2 bg-[#FEB800] text-black text-xs px-3 py-1 rounded font-bold shadow-lg">CARD</span>
                    <span class="absolute bottom-2 left-2 bg-black bg-opacity-60 text-white text-xs px-2 py-1 rounded">${fileSizeMB} MB</span>
                `;
                
                previewContainer.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }
}

function selectCardImage(index) {
    selectedCardIndex = index;
    document.getElementById('card_image_index').value = index;
    
    console.log('Imagem selecionada como CARD:', index);
    
    // Atualiza todas as imagens
    const previewContainer = document.getElementById('imagePreview');
    const totalImages = previewContainer.children.length;
    
    for (let i = 0; i < totalImages; i++) {
        const img = document.querySelector(`.image-border-${i}`);
        const badge = document.querySelector(`.card-badge-${i}`);
        
        if (img && badge) {
            if (i === index) {
                img.classList.remove('border-gray-300');
                img.classList.add('border-[#FEB800]');
                badge.classList.remove('hidden');
            } else {
                img.classList.remove('border-[#FEB800]');
                img.classList.add('border-gray-300');
                badge.classList.add('hidden');
            }
        }
    }
}

// Preview da logo
function previewLogo(event) {
    const previewContainer = document.getElementById('logoPreview');
    const file = event.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewContainer.innerHTML = `
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                    <img src="${e.target.result}" class="w-24 h-24 object-cover rounded-lg border-2 border-[#FEB800]">
                    <div>
                        <p class="font-semibold text-gray-700">Logo selecionada</p>
                        <p class="text-sm text-gray-500">${file.name}</p>
                    </div>
                </div>
            `;
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
                console.log('Endereço:', data.display_name);
                
                // Preenche o campo de localização (texto)
                const locationInput = document.querySelector('input[name="location"]');
                if (locationInput) {
                    locationInput.value = data.display_name;
                }
                
                // Gera e preenche o link do Uber
                const uberUrlInput = document.querySelector('input[name="uber_url"]');
                if (uberUrlInput) {
                    const uberUrl = `uber://riderequest?pickup[latitude]=${lat.toFixed(6)}&pickup[longitude]=${lng.toFixed(6)}&pickup[nickname]=Local&pickup[formatted_address]=${encodeURIComponent(data.display_name)}`;
                    uberUrlInput.value = uberUrl;
                }
                
                // Salvar dados no localStorage
                saveFormData();
            }
        })
        .catch(error => {
            console.error('Erro ao buscar endereço:', error);
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
                        
                        console.log('Endereço encontrado:', result.display_name);
                        
                        // Preenche o campo de localização (texto)
                        const locationInput = document.querySelector('input[name="location"]');
                        if (locationInput) {
                            locationInput.value = result.display_name;
                        }
                        
                        // Gera e preenche o link do Uber
                        const uberUrlInput = document.querySelector('input[name="uber_url"]');
                        if (uberUrlInput) {
                            const uberUrl = `uber://riderequest?pickup[latitude]=${lat.toFixed(6)}&pickup[longitude]=${lng.toFixed(6)}&pickup[nickname]=Local&pickup[formatted_address]=${encodeURIComponent(result.display_name)}`;
                            uberUrlInput.value = uberUrl;
                        }
                        
                        // Salvar dados no localStorage
                        saveFormData();
                    } else {
                        alert('Endereço não encontrado. Tente novamente.');
                    }
                })
                .catch(error => {
                    console.error('Erro na busca:', error);
                    alert('Erro ao buscar endereço.');
                });
        }
    }
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

// Categories system
const allCategories = @json($categories);

console.log('Todas as categorias:', allCategories);

// Load categories based on selected type
function loadCategories() {
    const typeSelect = document.getElementById('type');
    const typeName = typeSelect.value; // "lugar", "restaurante", "evento"
    const container = document.getElementById('categoriesContainer');
    
    console.log('Tipo selecionado:', typeName);
    
    if (!typeName) {
        container.innerHTML = '<p class="text-gray-500 text-sm col-span-full">Selecione um tipo primeiro para ver as categorias disponíveis</p>';
        return;
    }
    
    // Filter categories by matching type name (case insensitive)
    const filteredCategories = allCategories.filter(cat => {
        const catTypeName = cat.tipe && cat.tipe.name ? cat.tipe.name.toLowerCase() : '';
        return catTypeName === typeName.toLowerCase();
    });
    
    console.log('Categorias filtradas:', filteredCategories);
    
    if (filteredCategories.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm col-span-full">Nenhuma categoria disponível para este tipo</p>';
        return;
    }
    
    let html = '';
    filteredCategories.forEach(category => {
        html += `
            <label class="flex items-center space-x-2 p-3 border rounded-lg cursor-pointer hover:bg-[#FEB800] hover:bg-opacity-10 hover:border-[#FEB800] transition">
                <input type="checkbox" name="categories[]" value="${category.id}" 
                    class="w-4 h-4 text-[#FEB800] border-gray-300 rounded focus:ring-[#FEB800]">
                <span class="text-sm font-medium text-gray-700">${category.name}</span>
            </label>
        `;
    });
    
    container.innerHTML = html;
}

// Load categories on page load if type is already selected
const typeSelectElement = document.getElementById('type');
if (typeSelectElement && typeSelectElement.value) {
    loadCategories();
}

// Restore previously selected categories
@if(old('categories'))
    const oldCategories = @json(old('categories'));
    setTimeout(() => {
        oldCategories.forEach(catId => {
            const checkbox = document.querySelector(`input[name="categories[]"][value="${catId}"]`);
            if (checkbox) {
                checkbox.checked = true;
                checkbox.closest('label').classList.add('bg-[#FEB800]', 'bg-opacity-10', 'border-[#FEB800]');
            }
        });
    }, 100);
@endif

// Add visual feedback when categories are checked/unchecked
document.addEventListener('change', function(e) {
    if (e.target.name === 'categories[]') {
        const label = e.target.closest('label');
        if (e.target.checked) {
            label.classList.add('bg-[#FEB800]', 'bg-opacity-10', 'border-[#FEB800]');
        } else {
            label.classList.remove('bg-[#FEB800]', 'bg-opacity-10', 'border-[#FEB800]');
        }
    }
});
</script>
@endsection