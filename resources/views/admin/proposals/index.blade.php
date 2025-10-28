@extends('layouts.admin')

@section('title', 'Propostas')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Propostas</h1>
    <p class="text-gray-600 mt-1">Todas as propostas entre proprietários e influenciadores</p>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Influenciador</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lugar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($proposals as $proposal)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $proposal->id }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        @if($proposal->influencer->avatar)
                        <img src="{{ $proposal->influencer->avatar }}" alt="{{ $proposal->influencer->name }}" class="w-8 h-8 rounded-full mr-2">
                        @else
                        <div class="w-8 h-8 rounded-full bg-yellow-500 flex items-center justify-center text-white mr-2">
                            {{ substr($proposal->influencer->name, 0, 1) }}
                        </div>
                        @endif
                        <span class="text-sm text-gray-900">{{ $proposal->influencer->name }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $proposal->place->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    R$ {{ number_format($proposal->amount, 2, ',', '.') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full
                        {{ $proposal->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $proposal->status == 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $proposal->status == 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $proposal->status == 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                        @if($proposal->status == 'pending') Pendente
                        @elseif($proposal->status == 'accepted') Aceita
                        @elseif($proposal->status == 'completed') Completa
                        @elseif($proposal->status == 'rejected') Rejeitada
                        @endif
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $proposal->created_at->format('d/m/Y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    Nenhuma proposta encontrada
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $proposals->links() }}
</div>
@endsection
