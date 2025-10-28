@extends('layouts.admin')

@section('title', 'Banners')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Banners</h1>
        <p class="text-gray-600 mt-1">Gerencie os banners da plataforma</p>
    </div>
    <a href="{{ route('admin.banners.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i>Novo Banner
    </a>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preview</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Link</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($banners as $banner)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $banner->id }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-20 h-12 object-cover rounded">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $banner->title }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                    @if($banner->link)
                    <a href="{{ $banner->link }}" target="_blank" class="hover:underline">
                        <i class="fas fa-external-link-alt"></i> Ver link
                    </a>
                    @else
                    -
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full {{ $banner->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $banner->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <a href="{{ route('admin.banners.edit', $banner->id) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.banners.delete', $banner->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza?')">
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
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    Nenhum banner encontrado
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $banners->links() }}
</div>
@endsection
