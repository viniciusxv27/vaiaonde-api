@extends('layouts.partner')

@section('title', 'Meu Perfil')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Meu Perfil</h1>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if(!Auth::user()->cpf || !Auth::user()->phone)
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                <div>
                    <p class="font-semibold">Atenção: Dados incompletos para pagamentos PIX</p>
                    <p class="text-sm mt-1">
                        Para utilizar pagamentos via PIX, é necessário cadastrar:
                        @if(!Auth::user()->cpf) <strong>CPF</strong> @endif
                        @if(!Auth::user()->cpf && !Auth::user()->phone) e @endif
                        @if(!Auth::user()->phone) <strong>Telefone</strong> @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('partner.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Informações Pessoais</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', Auth::user()->name) }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                        <input type="email" name="email" id="email" value="{{ old('email', Auth::user()->email) }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror" required>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', Auth::user()->phone) }}" 
                            placeholder="(00) 00000-0000"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Necessário para pagamentos PIX</p>
                    </div>

                    <div>
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                        <input type="text" name="cpf" id="cpf" value="{{ old('cpf', Auth::user()->cpf) }}" 
                            placeholder="000.000.000-00"
                            maxlength="14"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('cpf') border-red-500 @enderror">
                        @error('cpf')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Necessário para pagamentos PIX</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Conta</label>
                        <input type="text" value="{{ Auth::user()->role == 'proprietario' ? 'Parceiro/Proprietário' : ucfirst(Auth::user()->role) }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" disabled>
                    </div>

                    <div>
                        <label for="pix_key" class="block text-sm font-medium text-gray-700 mb-1">Chave PIX (para saques)</label>
                        <input type="text" name="pix_key" id="pix_key" value="{{ old('pix_key', Auth::user()->pix_key) }}" 
                            placeholder="CPF, E-mail, Telefone ou Chave Aleatória"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('pix_key') border-red-500 @enderror">
                        @error('pix_key')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-6">

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Alterar Senha</h2>
                <p class="text-sm text-gray-600 mb-4">Deixe em branco se não quiser alterar a senha</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Senha Atual</label>
                        <input type="password" name="current_password" id="current_password" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                        <input type="password" name="password" id="password" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <hr class="my-6">

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Carteira</h2>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Saldo Atual</p>
                            <p class="text-3xl font-bold text-blue-600">R$ {{ number_format(Auth::user()->wallet_balance ?? 0, 2, ',', '.') }}</p>
                        </div>
                        <a href="{{ route('partner.wallet') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition">
                            Gerenciar Carteira
                        </a>
                    </div>
                </div>
            </div>

            @if(Auth::user()->partnerSubscription)
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Assinatura Atual</h2>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Plano Atual</p>
                            <p class="text-2xl font-bold text-purple-600">{{ Auth::user()->partnerSubscription->plan->name }}</p>
                            <p class="text-sm text-gray-600 mt-2">
                                Renova em: {{ Auth::user()->partnerSubscription->next_payment_date->format('d/m/Y') }}
                            </p>
                        </div>
                        <a href="{{ route('partner.plans') }}" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-lg transition">
                            Ver Planos
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-save mr-2"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Máscara para CPF
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;
    }
});

// Máscara para Telefone
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        if (value.length <= 10) {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        }
        e.target.value = value;
    }
});
</script>
@endsection