@extends('layouts.admin')

@section('title', 'Criar Categoria')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.categories') }}" class="text-blue-600 hover:text-blue-800 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Nova Categoria</h1>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="tipe_id" class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                <select name="tipe_id" id="tipe_id" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('tipe_id') border-red-500 @enderror" required>
                    <option value="">Selecione o tipo</option>
                    @foreach($tipes as $tipe)
                        <option value="{{ $tipe->id }}" {{ old('tipe_id') == $tipe->id ? 'selected' : '' }}>
                            {{ $tipe->name }}
                        </option>
                    @endforeach
                </select>
                @error('tipe_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome da Categoria *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" required>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.categories') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold transition">
                    Cancelar
                </a>
                <button type="submit" class="bg-[#FEB800] hover:bg-[#e5a700] text-black px-6 py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-save mr-2"></i>Salvar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
