{{-- Formulaire partagé par create.blade.php et edit.blade.php --}}
@csrf
@if (($formMode ?? 'create') === 'edit')
    @method('PUT')
@endif

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <label class="block text-sm font-medium text-slate-700">Nom <span class="text-rose-500">*</span>
        <input type="text" name="nom" value="{{ old('nom', $apprenant->nom ?? '') }}" required maxlength="100" autocomplete="family-name" placeholder="Ex. KOUASSI" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        @error('nom') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">Prénoms <span class="text-rose-500">*</span>
        <input type="text" name="prenoms" value="{{ old('prenoms', $apprenant->prenoms ?? '') }}" required maxlength="150" autocomplete="given-name" placeholder="Ex. Jean Marc" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        @error('prenoms') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">Adresse e-mail <span class="text-rose-500">*</span>
        <input type="email" name="email" value="{{ old('email', $apprenant->email ?? '') }}" required maxlength="255" autocomplete="email" placeholder="nom@exemple.com" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        @error('email') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">Téléphone
        <input type="tel" name="telephone" value="{{ old('telephone', $apprenant->telephone ?? '') }}" maxlength="30" autocomplete="tel" placeholder="+225 0700000000" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        @error('telephone') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">Date de naissance
        <input type="date" name="date_naissance" value="{{ old('date_naissance', isset($apprenant) && $apprenant->date_naissance ? \Carbon\Carbon::parse($apprenant->date_naissance)->format('Y-m-d') : '') }}" max="{{ now()->format('Y-m-d') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        @error('date_naissance') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">Sexe
        <select name="sexe" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <option value="">Choisir le sexe</option>
            <option value="Masculin" @selected(old('sexe', $apprenant->sexe ?? '') === 'Masculin')>Masculin</option>
            <option value="Féminin" @selected(old('sexe', $apprenant->sexe ?? '') === 'Féminin')>Féminin</option>
        </select>
        @error('sexe') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>
   
    <label class="block text-sm font-medium text-slate-700 sm:col-span-2">Nationalité
        <input type="text" name="nationalite" value="{{ old('nationalite', $apprenant->nationalite ?? '') }}" maxlength="100" placeholder="Ex. Ivoirienne" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        @error('nationalite') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>

    {{-- Un seul et unique champ Vacation --}}
    <label class="block text-sm font-medium text-slate-700">Vacation<span class="text-rose-500">*</span>
        <select name="vacation_id" id="vacation_id" required
                class="mt-2 w-full rounded-xl border bg-white px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('vacation_id') border-rose-500 @else border-slate-200 @enderror">
            <option value="">-- Choisir la vacation --</option>
            @foreach ($vacations ?? [] as $vacation)
                <option value="{{ $vacation->id_vacation }}"
                    @selected((string) old('vacation_id', $apprenant->vacation_id ?? '') === (string) $vacation->id_vacation)>
                    {{ $vacation->libelle }}
                </option>
            @endforeach
        </select>
        @error('vacation_id') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </label>

    {{-- Préparation de la sélection des formations --}}


    <fieldset class="sm:col-span-2">
        <legend class="mb-3 text-sm font-semibold text-slate-700">Formations associées <span class="text-rose-500">*</span></legend>
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($formations as $formation)
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 text-sm hover:bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="formations[]" value="{{ $formation->id_formation }}"
                           @checked(in_array((string) $formation->id_formation, $selectedFormations, true))
                           class="mt-1">
                    <span class="block font-semibold text-slate-700">{{ $formation->intitule }}</span>
                </label>
            @endforeach
        </div>
        @error('formations') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </fieldset>
</div>