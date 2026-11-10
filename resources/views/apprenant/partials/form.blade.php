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
    <input type="text" 
           id="datepicker1" 
           name="date_naissance" 
           value="{{ old('date_naissance', isset($apprenant) && $apprenant->date_naissance ? \Carbon\Carbon::parse($apprenant->date_naissance)->format('Y-m-d') : '') }}" 
           placeholder="Sélectionnez une date..." 
           autocomplete="off"
           class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
    @error('date_naissance') 
        <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> 
    @enderror
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
                    <input type="checkbox" name="formations[]" value="{{ $formation->id_formation }}" data-prix="{{ (int) $formation->prix }}"
                           @checked(in_array((string) $formation->id_formation, $selectedFormations, true))
                           class="mt-1">
                    <span class="block font-semibold text-slate-700">{{ $formation->intitule }}
                        <span class="block text-xs font-normal text-slate-500">{{ number_format((int) $formation->prix, 0, ',', ' ') }} FCFA</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('formations') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
    </fieldset>

    @if (($formMode ?? 'create') === 'create')
        {{-- Premier versement (acompte) : retranché du total de l'ensemble des formations --}}
        <fieldset class="sm:col-span-2 rounded-xl border border-emerald-200 bg-emerald-50/40 p-4" data-paiement-box>
            <legend class="px-2 text-sm font-semibold text-slate-700">Paiement à l’inscription (versement n°1 sur {{ \App\Service\PaiementService::MAX_VERSEMENTS }})</legend>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">Acompte (FCFA) <span class="text-rose-500">*</span>
                    <input type="number" name="acompte" id="acompte" min="1" step="1" value="{{ old('acompte') }}" required
                           class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('acompte') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-medium text-slate-700">Mode de paiement
                    <select name="mode_paiement" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @foreach (\App\Enum\ModePaiement::cases() as $mode)
                            <option value="{{ $mode->value }}" @selected(old('mode_paiement', 'espece') === $mode->value)>{{ $mode->label() }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <p class="mt-3 text-sm text-slate-600">Total des formations : <strong id="paiement-total">0</strong> FCFA
                · Reste après acompte : <strong id="paiement-reste">0</strong> FCFA</p>
            <p class="mt-1 text-xs text-slate-500">Le reste est payable librement, en 3 versements maximum, avant la fin de la cohorte.</p>
        </fieldset>
        <script>

            document.addEventListener('DOMContentLoaded', function () {
    // S'assure que l'élément existe bien sur la page avant d'initialiser Flatpickr
    if (document.getElementById('datepicker1')) {
        flatpickr("#datepicker1", {
            dateFormat: "Y-m-d",      // Format envoyé à Laravel (AAAA-MM-JJ)
            altInput: true,           // Affiche un format plus lisible pour l'utilisateur
            altFormat: "d/m/Y",       // Format affiché (JJ/MM/AAAA)
            maxDate: "today",         // Empêche de sélectionner une date future
            locale: "fr"              // Mois et jours en français
        });
    }
});
            (function () {
                const fmt = n => new Intl.NumberFormat('fr-FR').format(n);
                const boxes = document.querySelectorAll('input[name="formations[]"]');
                const acompte = document.getElementById('acompte');
                function maj() {
                    let total = 0;
                    boxes.forEach(b => { if (b.checked) total += parseInt(b.dataset.prix || '0', 10); });
                    const x = parseInt(acompte.value || '0', 10);
                    document.getElementById('paiement-total').textContent = fmt(total);
                    document.getElementById('paiement-reste').textContent = fmt(Math.max(0, total - x));
                    acompte.max = total || '';
                }
                boxes.forEach(b => b.addEventListener('change', maj));
                acompte.addEventListener('input', maj);
                maj();
            })();
        </script>
    @endif
</div>