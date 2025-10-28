@extends('layouts.admin')

@section('title', 'Lugares')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Lugares</h1>
        <p class="text-gray-600 mt-1">Todos os estabelecimentos cadastrados</p>
    </div>
    <a href="{{ route('admin.places.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i>Novo Lugar
    </a>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cidade</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proprietário</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menções</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cadastro</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($places as $place)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $place->id }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        @if($place->image)
                        <img src="{{ $place->image }}" alt="{{ $place->name }}" class="w-10 h-10 rounded object-cover mr-3">
                        @else
                        <div class="w-10 h-10 rounded bg-blue-500 flex items-center justify-center text-white mr-3">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        @endif
                        <span class="text-sm font-medium text-gray-900">{{ $place->name }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $place->city->name ?? 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $place->owner->name ?? 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ \App\Models\Video::where('place_id', $place->id)->count() }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $place->created_at->format('d/m/Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <form action="{{ route('admin.places.delete', $place->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    Nenhum lugar encontrado
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $places->links() }}
</div>
@endsection
