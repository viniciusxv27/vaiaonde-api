@extends('layouts.partner')

@section('title', 'Configurações')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Configurações</h1>

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

    <!-- Notification Settings -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
            <i class="fas fa-bell text-blue-500 mr-2"></i>Notificações
        </h2>
        
        <form action="{{ route('partner.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <h3 class="font-medium text-gray-800">Notificações por E-mail</h3>
                        <p class="text-sm text-gray-600">Receba alertas sobre novos vídeos, propostas e atualizações</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notification_email" class="sr-only peer" {{ old('notification_email', true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <h3 class="font-medium text-gray-800">Notificações por SMS</h3>
                        <p class="text-sm text-gray-600">Receba alertas importantes via mensagem de texto</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notification_sms" class="sr-only peer" {{ old('notification_sms', false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>

            <hr class="my-6">

            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-crown text-purple-500 mr-2"></i>Assinatura
            </h2>

            @if(Auth::user()->partnerSubscription)
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <div>
                        <h3 class="font-medium text-gray-800">Renovação Automática</h3>
                        <p class="text-sm text-gray-600">Renovar automaticamente a assinatura todo mês</p>
                        <p class="text-xs text-purple-600 mt-1">
                            Plano atual: {{ Auth::user()->partnerSubscription->plan->name }} - 
                            R$ {{ number_format(Auth::user()->partnerSubscription->plan->price, 2, ',', '.') }}/mês
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auto_renew_subscription" class="sr-only peer" 
                            {{ old('auto_renew_subscription', Auth::user()->partnerSubscription->auto_renew) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-blue-900">Sobre a renovação automática</h4>
                            <p class="text-sm text-blue-800 mt-1">
                                Com a renovação automática ativada, o valor da assinatura será debitado automaticamente 
                                da sua carteira na data de vencimento. Certifique-se de manter saldo suficiente para evitar 
                                a suspensão dos seus lugares.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-yellow-900">Nenhuma assinatura ativa</h4>
                        <p class="text-sm text-yellow-800 mt-1">
                            Você não possui uma assinatura ativa no momento. 
                            <a href="{{ route('partner.plans') }}" class="underline font-medium">Clique aqui</a> 
                            para ver os planos disponíveis.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-save mr-2"></i>Salvar Configurações
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="bg-white shadow-md rounded-lg p-6 border-2 border-red-200">
        <h2 class="text-xl font-semibold text-red-600 mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>Zona de Perigo
        </h2>
        
        <div class="space-y-4">
            @if(Auth::user()->partnerSubscription && Auth::user()->partnerSubscription->status == 'active')
            <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                <div>
                    <h3 class="font-medium text-gray-800">Cancelar Assinatura</h3>
                    <p class="text-sm text-gray-600">Você ainda terá acesso até {{ Auth::user()->partnerSubscription->ends_at->format('d/m/Y') }}</p>
                </div>
                <form action="{{ route('partner.subscription.cancel') }}" method="POST" 
                    onsubmit="return confirm('Tem certeza que deseja cancelar sua assinatura? Você ainda terá acesso até o fim do período pago.')">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-medium transition">
                        Cancelar Assinatura
                    </button>
                </form>
            </div>
            @endif

            <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                <div>
                    <h3 class="font-medium text-gray-800">Excluir Conta</h3>
                    <p class="text-sm text-gray-600">Esta ação não pode ser desfeita</p>
                </div>
                <button onclick="alert('Entre em contato com o suporte para excluir sua conta.')" 
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition">
                    Excluir Conta
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
