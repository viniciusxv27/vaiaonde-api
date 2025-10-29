@extends('layouts.partner')

@section('title', 'Cadastrar Estabelecimento')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center mb-6">
        <a href="{{ route('partner.places') }}" class="text-blue-600 hover:text-blue-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Cadastrar Estabelecimento</h1>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if(!Auth::user()->partnerSubscription || !Auth::user()->partnerSubscription->isActive())
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Você pode cadastrar seu estabelecimento gratuitamente! Para desbloquear recursos premium como 
                        <strong>promoções, destaque na busca e vídeos profissionais</strong>, 
                        <a href="{{ route('partner.plans') }}" class="font-medium underline">assine um plano</a>.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('partner.places.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nome -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome do Estabelecimento *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo -->
                <div>
                    <label for="tipe_id" class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="tipe_id" id="tipe_id" onchange="loadCategories()" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FEB800] focus:border-transparent @error('tipe_id') border-red-500 @enderror" required>
                        <option value="">Selecione o tipo</option>
                        @foreach($tipes as $tipe)
                            <option value="{{ $tipe->id }}" {{ old('tipe_id') == $tipe->id ? 'selected' : '' }}>
                                {{ ucfirst($tipe->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipe_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cidade -->
                <div>
                    <label for="city_id" class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
                    <select name="city_id" id="city_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FEB800] focus:border-transparent @error('city_id') border-red-500 @enderror" required>
                        <option value="">Selecione a cidade</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('city_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Categorias (Múltiplas) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categorias</label>
                    <div id="categoriesContainer" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <p class="text-gray-500 text-sm col-span-full">Selecione um tipo primeiro para ver as categorias disponíveis</p>
                    </div>
                    @error('categories')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telefone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FEB800] focus:border-transparent @error('phone') border-red-500 @enderror"
                        placeholder="(00) 00000-0000">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Instagram -->
                <div>
                    <label for="instagram_url" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fab fa-instagram mr-1"></i>Instagram
                    </label>
                    <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FEB800] focus:border-transparent @error('instagram_url') border-red-500 @enderror"
                        placeholder="https://instagram.com/...">
                    @error('instagram_url')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Link Google Maps -->
                <div>
                    <label for="location_url" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-map-marker-alt mr-1"></i>Link Google Maps
                    </label>
                    <input type="url" name="location_url" id="location_url" value="{{ old('location_url') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FEB800] focus:border-transparent @error('location_url') border-red-500 @enderror"
                        placeholder="https://maps.google.com/...">
                    @error('location_url')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Link Uber -->
                <div>
                    <label for="uber_url" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-car mr-1"></i>Link Uber
                    </label>
                    <input type="url" name="uber_url" id="uber_url" value="{{ old('uber_url') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FEB800] focus:border-transparent @error('uber_url') border-red-500 @enderror"
                        placeholder="https://uber.com/...">
                    @error('uber_url')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Localização (Texto) -->
                <div class="md:col-span-2">
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-location-dot mr-1"></i>Localização (Texto)
                    </label>
                    <input type="text" name="location" id="location" value="{{ old('location') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FEB800] focus:border-transparent @error('location') border-red-500 @enderror"
                        placeholder="Ex: Centro, Vitória - ES">
                    @error('location')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Logo -->
                <div class="md:col-span-2">
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-image mr-1"></i>Logo do Estabelecimento *
                    </label>
                    <input type="file" name="logo" id="logo" accept="image/*" required onchange="previewLogo(event)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('logo') border-red-500 @enderror">
                    @error('logo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle"></i> Logo/ícone do estabelecimento (será exibido em miniatura)
                    </p>
                    <div id="logoPreview" class="mt-4"></div>
                </div>

                <!-- Imagens (Múltiplas) -->
                <div class="md:col-span-2">
                    <label for="images" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-images mr-1"></i>Imagens do Estabelecimento (Múltiplas) *
                    </label>
                    <input type="file" name="images[]" id="images" accept="image/*" multiple required onchange="previewMultipleImages(event)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('images.*') border-red-500 @enderror">
                    <input type="hidden" name="card_image_index" id="card_image_index" value="0">
                    @error('images')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle"></i> Selecione múltiplas imagens (máx. 5MB cada). Clique em uma imagem para defini-la como principal (imagem do card).
                    </p>
                    <div id="imagePreview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"></div>
                </div>

                <!-- Descrição -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="description" id="description" rows="4" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Endereço -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                    <div class="flex gap-2">
                        <input type="text" name="address" id="address" value="{{ old('address') }}" 
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
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('partner.places') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold transition">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-save mr-2"></i>Cadastrar Estabelecimento
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Salvar e restaurar dados do formulário
    const FORM_DATA_KEY = 'partner_place_form_data';

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
                        if (marker) map.removeLayer(marker);
                        marker = L.marker([lat, lng]).addTo(map);
                    }, 500);
                }
            }
            
            if (data.location) document.querySelector('input[name="location"]').value = data.location;
            if (data.uber_url) document.querySelector('input[name="uber_url"]').value = data.uber_url;
            if (data.location_url) document.querySelector('input[name="location_url"]').value = data.location_url;
            if (data.address) document.getElementById('address').value = data.address;
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
            address: document.getElementById('address').value,
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

    // Initialize map
    const map = L.map('map').setView([-15.7942, -47.8822], 4); // Brazil center

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker;

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
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lon = parseFloat(result.lon);
                    
                    map.setView([lat, lon], 15);
                    
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    
                    marker = L.marker([lat, lon]).addTo(map);
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lon;
                    
                    // Preenche o campo de localização (texto)
                    const locationInput = document.querySelector('input[name="location"]');
                    if (locationInput) {
                        locationInput.value = result.display_name;
                    }
                    
                    // Gera e preenche o link do Uber
                    const uberUrlInput = document.querySelector('input[name="uber_url"]');
                    if (uberUrlInput) {
                        const uberUrl = `uber://riderequest?pickup[latitude]=${lat.toFixed(6)}&pickup[longitude]=${lon.toFixed(6)}&pickup[nickname]=Local&pickup[formatted_address]=${encodeURIComponent(result.display_name)}`;
                        uberUrlInput.value = uberUrl;
                    }
                    
                    // Salvar dados no localStorage
                    saveFormData();
                } else {
                    alert('Endereço não encontrado');
                }
            })
            .catch(error => {
                console.error('Erro na busca:', error);
                alert('Erro ao buscar endereço.');
            });
    }

    // Categories data
    const allCategories = @json($categories);
    
    console.log('Todas as categorias:', allCategories);

    // Load categories based on selected type
    function loadCategories() {
        const tipeId = document.getElementById('tipe_id').value;
        const container = document.getElementById('categoriesContainer');
        
        console.log('Tipo selecionado:', tipeId);
        
        if (!tipeId) {
            container.innerHTML = '<p class="text-gray-500 text-sm col-span-full">Selecione um tipo primeiro para ver as categorias disponíveis</p>';
            return;
        }
        
        const filteredCategories = allCategories.filter(cat => cat.tipe_id == tipeId);
        
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

    // Load categories on page load if type is already selected (from old input)
    document.addEventListener('DOMContentLoaded', function() {
        const tipeId = document.getElementById('tipe_id').value;
        if (tipeId) {
            loadCategories();
            
            // Restore previously selected categories
            @if(old('categories'))
                const oldCategories = @json(old('categories'));
                setTimeout(() => {
                    oldCategories.forEach(catId => {
                        const checkbox = document.querySelector(`input[name="categories[]"][value="${catId}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                            // Add visual feedback for selected
                            checkbox.closest('label').classList.add('bg-[#FEB800]', 'bg-opacity-10', 'border-[#FEB800]');
                        }
                    });
                }, 100);
            @endif
        }
    });
    
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
