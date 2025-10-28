@extends('layouts.partner')

@section('title', 'Planos de Assinatura')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Planos de Assinatura</h1>
    <p class="text-gray-600 mt-1">Escolha o melhor plano para o seu negócio</p>
</div>

<!-- Assinatura Atual -->
@if(auth()->user()->partnerSubscription && auth()->user()->partnerSubscription->isActive())
<div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 mb-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold">Seu Plano Atual</h3>
            <p class="text-2xl font-bold mt-2">{{ auth()->user()->partnerSubscription->plan->name }}</p>
            <p class="mt-2">Renova em: {{ auth()->user()->partnerSubscription->next_payment_date->format('d/m/Y') }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm opacity-90">Valor Mensal</p>
            <p class="text-3xl font-bold">R$ {{ number_format(floatval(auth()->user()->partnerSubscription->plan->price), 2, ',', '.') }}</p>
        </div>
    </div>
</div>
@endif

<!-- Cards de Planos -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    @foreach(\App\Models\PartnerSubscriptionPlan::where('is_active', true)->orderBy('price')->get() as $plan)
    <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ $plan->name == 'Plano Destaque' ? 'ring-4 ring-yellow-400' : '' }}">
        @if($plan->name == 'Plano Destaque')
        <div class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-center py-2 font-bold">
            <i class="fas fa-crown mr-2"></i>MAIS POPULAR
        </div>
        @endif
        
        <div class="p-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $plan->name }}</h3>
            <div class="mb-4">
                <span class="text-4xl font-bold text-blue-600">R$ {{ number_format(floatval($plan->price), 0, ',', '.') }}</span>
                <span class="text-gray-600">/mês</span>
            </div>
            
            <p class="text-gray-600 text-sm mb-6">{{ $plan->description }}</p>
            
            <ul class="space-y-3 mb-6">
                @foreach($plan->features as $feature)
                <li class="flex items-start">
                    <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                    <span class="text-sm text-gray-700">{{ $feature }}</span>
                </li>
                @endforeach
            </ul>
            
            @if(auth()->user()->partnerSubscription && auth()->user()->partnerSubscription->plan_id == $plan->id)
                <button disabled class="w-full py-3 bg-gray-300 text-gray-600 rounded-lg font-semibold cursor-not-allowed">
                    Plano Atual
                </button>
            @else
                <form method="POST" action="{{ route('partner.subscription.subscribe') }}">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <button 
                        type="submit"
                        class="w-full py-3 {{ $plan->price > 0 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-600 hover:bg-gray-700' }} text-white rounded-lg font-semibold transition"
                        {{ $plan->price > 0 && auth()->user()->wallet_balance < $plan->price ? 'disabled title=Saldo insuficiente' : '' }}
                    >
                        @if($plan->price == 0)
                            Começar Grátis
                        @else
                            Assinar Agora
                        @endif
                    </button>
                </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

<!-- FAQ -->
<div class="mt-12 bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Perguntas Frequentes</h2>
    
    <div class="space-y-4" x-data="{ open: null }">
        <div class="border-b pb-4">
            <button @click="open = open === 1 ? null : 1" class="flex items-center justify-between w-full text-left">
                <span class="font-semibold">Como funciona o pagamento?</span>
                <i class="fas" :class="open === 1 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="open === 1" class="mt-3 text-gray-600 text-sm">
                O pagamento é debitado automaticamente da sua carteira todo mês. Certifique-se de manter saldo suficiente para evitar a suspensão do serviço.
            </div>
        </div>
        
        <div class="border-b pb-4">
            <button @click="open = open === 2 ? null : 2" class="flex items-center justify-between w-full text-left">
                <span class="font-semibold">O que acontece se eu não pagar?</span>
                <i class="fas" :class="open === 2 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="open === 2" class="mt-3 text-gray-600 text-sm">
                Seu lugar será desativado automaticamente até a regularização do pagamento. Você pode reativar a qualquer momento adicionando saldo à carteira.
            </div>
        </div>
        
        <div class="border-b pb-4">
            <button @click="open = open === 3 ? null : 3" class="flex items-center justify-between w-full text-left">
                <span class="font-semibold">Posso mudar de plano?</span>
                <i class="fas" :class="open === 3 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="open === 3" class="mt-3 text-gray-600 text-sm">
                Sim! Você pode fazer upgrade ou downgrade do seu plano a qualquer momento. As alterações entram em vigor no próximo ciclo de cobrança.
            </div>
        </div>
        
        <div class="pb-4">
            <button @click="open = open === 4 ? null : 4" class="flex items-center justify-between w-full text-left">
                <span class="font-semibold">Como funciona o vídeo profissional do Plano Destaque?</span>
                <i class="fas" :class="open === 4 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="open === 4" class="mt-3 text-gray-600 text-sm">
                Todo mês você terá direito a 1 vídeo profissional produzido por um influenciador cadastrado em nossa plataforma, promovendo seu negócio.
            </div>
        </div>
    </div>
</div>
@endsection
