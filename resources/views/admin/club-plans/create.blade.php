@extends('layouts.admin')

@section('title', 'Criar Plano do Club')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.club-plans') }}" class="text-blue-600 hover:text-blue-700 mb-4 inline-flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Voltar para Planos
    </a>
    <h1 class="text-2xl font-bold text-gray-800 mt-2">Criar Novo Plano do Club</h1>
    <p class="text-gray-600 mt-1">Defina os benefícios e valores do plano</p>
</div>

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('admin.club-plans.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Título do Plano *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Preço (R$) *</label>
                <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('price') border-red-500 @enderror">
                @error('price')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Duração (dias) *</label>
            <select name="duration_days" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('duration_days') border-red-500 @enderror">
                <option value="30" {{ old('duration_days') == 30 ? 'selected' : '' }}>30 dias (Mensal)</option>
                <option value="90" {{ old('duration_days') == 90 ? 'selected' : '' }}>90 dias (Trimestral)</option>
                <option value="180" {{ old('duration_days') == 180 ? 'selected' : '' }}>180 dias (Semestral)</option>
                <option value="365" {{ old('duration_days') == 365 ? 'selected' : '' }}>365 dias (Anual)</option>
            </select>
            @error('duration_days')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
            <textarea name="description" rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
            @error('description')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Benefícios *</label>
            <div id="benefits-container" class="space-y-3">
                <div class="benefit-item flex gap-2">
                    <input type="text" name="benefits[]" placeholder="Digite um benefício" required
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <button type="button" onclick="removeBenefit(this)" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 hidden">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <button type="button" onclick="addBenefit()" class="mt-3 text-blue-600 hover:text-blue-700 text-sm font-medium">
                <i class="fas fa-plus mr-1"></i> Adicionar Benefício
            </button>
            @error('benefits')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                <i class="fas fa-save mr-2"></i>Salvar Plano
            </button>
            <a href="{{ route('admin.club-plans') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
function addBenefit() {
    const container = document.getElementById('benefits-container');
    const newBenefit = document.createElement('div');
    newBenefit.className = 'benefit-item flex gap-2';
    newBenefit.innerHTML = `
        <input type="text" name="benefits[]" placeholder="Digite um benefício" required
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        <button type="button" onclick="removeBenefit(this)" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(newBenefit);
    updateRemoveButtons();
}

function removeBenefit(button) {
    button.closest('.benefit-item').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const items = document.querySelectorAll('.benefit-item');
    items.forEach((item, index) => {
        const removeBtn = item.querySelector('button[onclick="removeBenefit(this)"]');
        if (items.length > 1) {
            removeBtn.classList.remove('hidden');
        } else {
            removeBtn.classList.add('hidden');
        }
    });
}

// Initial update
updateRemoveButtons();
</script>
@endsection
