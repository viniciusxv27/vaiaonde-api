@extends('layouts.influencer')

@section('title', 'Meu Perfil')
@section('page-title', 'Meu Perfil')
@section('page-subtitle', 'Gerencie suas informações pessoais')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Photo -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Foto de Perfil</h3>
            
            <div class="text-center">
                <div class="w-32 h-32 mx-auto bg-purple-600 rounded-full flex items-center justify-center text-white text-4xl mb-4 overflow-hidden">
                    @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                    <i class="fas fa-user"></i>
                    @endif
                </div>
                
                <div class="flex justify-center">
                    <label for="avatar_input" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition cursor-pointer">
                        <i class="fas fa-camera mr-2"></i>Alterar Foto
                    </label>
                </div>
                
                <p class="text-xs text-gray-500 mt-3">JPG, PNG ou GIF. Máx. 2MB</p>
            </div>
            
            <div class="mt-6 pt-6 border-t space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Membro desde</span>
                    <span class="text-sm font-semibold">{{ auth()->user()->created_at->format('M Y') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Tipo de conta</span>
                    <span class="text-sm font-semibold text-purple-600">Influenciador</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Status</span>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Ativo</span>
                </div>
            </div>
        </div>
        
        <!-- Social Stats -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h3 class="text-lg font-semibold mb-4">Estatísticas</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600"><i class="fas fa-video mr-2 text-blue-500"></i>Vídeos</span>
                    <span class="text-sm font-bold">{{ auth()->user()->videos_count ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600"><i class="fas fa-eye mr-2 text-purple-500"></i>Visualizações</span>
                    <span class="text-sm font-bold">{{ number_format(auth()->user()->total_views ?? 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600"><i class="fas fa-handshake mr-2 text-green-500"></i>Propostas</span>
                    <span class="text-sm font-bold">{{ auth()->user()->proposals_count ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-6">Informações Pessoais</h3>
            
            <form method="POST" action="{{ route('influencer.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Hidden file input for avatar -->
                <input type="file" id="avatar_input" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Nome Completo *</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" 
                            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                    
                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Nome de Usuário (@)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-500">@</span>
                            <input type="text" name="username" value="{{ old('username', auth()->user()->username) }}" 
                                class="w-full border rounded-lg pl-8 pr-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                                placeholder="seu_usuario">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Apenas letras, números e underscore (_)</p>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Email *</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" 
                            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                    
                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Telefone *</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" 
                            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                            placeholder="(00) 00000-0000" required>
                    </div>
                    
                    <!-- CPF -->
                    <div>
                        <label class="block text-sm font-medium mb-2">CPF *</label>
                        <input type="text" id="cpf" name="cpf" value="{{ old('cpf', auth()->user()->cpf) }}" 
                            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                            placeholder="000.000.000-00" required>
                        <p class="text-xs text-gray-500 mt-1">Necessário para pagamentos</p>
                    </div>
                    
                    <!-- PIX Key -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Chave PIX</label>
                        <input type="text" name="pix_key" value="{{ old('pix_key', auth()->user()->pix_key) }}" 
                            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                            placeholder="CPF, Email, Telefone ou Chave Aleatória">
                        <p class="text-xs text-gray-500 mt-1">Para receber saques via PIX</p>
                    </div>
                </div>
                
                <!-- Bio -->
                <div class="mt-6">
                    <label class="block text-sm font-medium mb-2">Biografia</label>
                    <textarea name="bio" rows="4" 
                        class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                        placeholder="Conte um pouco sobre você e seu trabalho...">{{ old('bio', auth()->user()->bio) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Máximo 500 caracteres</p>
                </div>
                
                <!-- Social Links -->
                <div class="mt-6">
                    <h4 class="font-semibold mb-4">Redes Sociais</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Instagram -->
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                <i class="fab fa-instagram text-pink-500 mr-2"></i>Instagram
                            </label>
                            <input type="text" name="instagram_url" value="{{ old('instagram_url', auth()->user()->instagram_url) }}" 
                                class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                                placeholder="https://instagram.com/seu_usuario">
                        </div>
                        
                        <!-- YouTube -->
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                <i class="fab fa-youtube text-red-500 mr-2"></i>YouTube
                            </label>
                            <input type="text" name="youtube_url" value="{{ old('youtube_url', auth()->user()->youtube_url) }}" 
                                class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                                placeholder="https://youtube.com/@seu_canal">
                        </div>
                        
                        <!-- TikTok -->
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                <i class="fab fa-tiktok mr-2"></i>TikTok
                            </label>
                            <input type="text" name="tiktok_url" value="{{ old('tiktok_url', auth()->user()->tiktok_url) }}" 
                                class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                                placeholder="https://tiktok.com/@seu_usuario">
                        </div>
                        
                        <!-- Twitter/X -->
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                <i class="fab fa-twitter text-blue-400 mr-2"></i>Twitter/X
                            </label>
                            <input type="text" name="twitter_url" value="{{ old('twitter_url', auth()->user()->twitter_url) }}" 
                                class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                                placeholder="https://twitter.com/seu_usuario">
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-save mr-2"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h3 class="text-lg font-semibold mb-6">Alterar Senha</h3>
            
            <form method="POST" action="{{ route('influencer.password.update') }}">
                @csrf
                @method('PUT')
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Senha Atual</label>
                        <input type="password" name="current_password" 
                            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Nova Senha</label>
                        <input type="password" name="password" 
                            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Confirmar Nova Senha</label>
                        <input type="password" name="password_confirmation" 
                            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-6 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-key mr-2"></i>Alterar Senha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Avatar preview
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const avatarContainer = document.querySelector('.w-32.h-32');
            avatarContainer.innerHTML = `<img src="${e.target.result}" alt="Profile" class="w-full h-full object-cover">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Phone mask
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
    }
    e.target.value = value;
});

// CPF mask
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    }
    e.target.value = value;
});
</script>
@endpush
