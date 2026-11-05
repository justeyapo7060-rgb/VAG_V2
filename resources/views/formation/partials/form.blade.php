{{-- Formulaire partagé par create.blade.php et edit.blade.php --}}
@csrf
@if (($formMode ?? 'create') === 'edit') @method('PUT') @endif
<div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    <label class="block text-sm font-medium text-slate-700 sm:col-span-2">Intitulé <span class="text-rose-500">*</span>
        <input type="text" name="intitule" value="{{ old('intitule', $formation->intitule ?? '') }}" required maxlength="255" placeholder="Ex. Pâtisserie française" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        @error('intitule') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>
    <label class="block text-sm font-medium text-slate-700 sm:col-span-2">Description <span class="text-rose-500">*</span>
        <textarea name="description" required rows="4" placeholder="Décris le contenu de la formation..." class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ old('description', $formation->description ?? '') }}</textarea>
        @error('description') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>
    <label class="block text-sm font-l text-slate-700">Prix (FCFA) <span class="text-rose-500">*</span>
        <input type="number" name="prix" value="{{ old('prix', $formation->prix ?? '') }}" required min="0" step="1" placeholder="Ex. 150000" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        @error('prix') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>
</div>
