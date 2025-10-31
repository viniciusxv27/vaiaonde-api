@extends('layouts.admin')

@section('title', 'Planos do Club')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Planos do Club</h1>
        <p class="text-gray-600 mt-1">Gerencie os planos de assinatura do Club de usuários</p>
    </div>
    <a href="{{ route('admin.club-plans.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
        <i class="fas fa-plus"></i>
        Novo Plano
    </a>
</div>

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($plans as $plan)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden {{ $plan->is_active ? 'border-2 border-blue-500' : 'opacity-75' }}">
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">{{ $plan->title }}</h3>
                    <p class="text-gray-600 text-sm mt-1">{{ $plan->duration_days }} dias</p>
                </div>
                @if($plan->is_active)
                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full font-semibold">
                    Ativo
                </span>
                @else
                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-semibold">
                    Inativo
                </span>
                @endif
            </div>

            @if($plan->description)
            <p class="text-gray-600 text-sm mb-4">{{ $plan->description }}</p>
            @endif

            <div class="mb-4">
                <div class="text-3xl font-bold text-gray-800">
                    R$ {{ number_format($plan->price, 2, ',', '.') }}
                </div>
                <div class="text-gray-500 text-sm">por {{ $plan->duration_days }} dias</div>
            </div>

            <div class="mb-6">
                <h4 class="font-semibold text-gray-700 mb-2 text-sm">Benefícios:</h4>
                <ul class="space-y-2">
                    @foreach($plan->benefits as $benefit)
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                        <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                        <span>{{ $benefit }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="flex gap-2 pt-4 border-t">
                <a href="{{ route('admin.club-plans.edit', $plan->id) }}" class="flex-1 text-center bg-blue-50 hover:bg-blue-100 text-blue-600 px-4 py-2 rounded transition">
                    <i class="fas fa-edit"></i> Editar
                </a>
                
                <form action="{{ route('admin.club-plans.toggle', $plan->id) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full {{ $plan->is_active ? 'bg-gray-50 hover:bg-gray-100 text-gray-600' : 'bg-green-50 hover:bg-green-100 text-green-600' }} px-4 py-2 rounded transition">
                        <i class="fas fa-{{ $plan->is_active ? 'pause' : 'play' }}"></i>
                        {{ $plan->is_active ? 'Desativar' : 'Ativar' }}
                    </button>
                </form>

                <form action="{{ route('admin.club-plans.delete', $plan->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este plano?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 bg-white rounded-lg shadow-sm p-12 text-center">
        <i class="fas fa-medal text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhum plano criado</h3>
        <p class="text-gray-500 mb-6">Crie o primeiro plano do Club para seus usuários</p>
        <a href="{{ route('admin.club-plans.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
            <i class="fas fa-plus"></i>
            Criar Primeiro Plano
        </a>
    </div>
    @endforelse
</div>
@endsection
