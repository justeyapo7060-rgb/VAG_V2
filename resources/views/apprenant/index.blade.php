{{-- La page hérite de la sidebar, du header et du footer définis dans base.blade.php. --}}
@extends('base')

@section('title', 'Apprenants')

@section('content')
    <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
        <div>
            <p class="mb-3 text-[11px] font-bold uppercase tracking-[.2em] text-atelier-blue">{{ now()->translatedFormat('l d F Y') }}</p>
            <h1 class="font-serif text-4xl font-bold tracking-tight text-atelier-ink sm:text-5xl">Apprenants</h1>
            <p class="mt-2 text-sm text-slate-500">Un suivi clair des parcours et de l’assiduité des apprenants.</p>
        </div>
        <div class="flex flex-wrap gap-3 xl:justify-end">
            <button type="button" id="exportBtn" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-atelier-ink shadow-sm transition hover:bg-slate-50">
                <i data-lucide="download" class="h-4 w-4"></i> Exporter
            </button>
            <a href="{{ route('apprenant.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-atelier-blue px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-blue-700">
                <i data-lucide="plus" class="h-4 w-4"></i> Nouvel apprenant
            </a>
        </div>
    </section>

    @if (session('success'))
        <div role="statut" class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif
    @php
        $learnersCollection = $apprenants instanceof \Illuminate\Pagination\AbstractPaginator
            ? collect($apprenants->items())
            : collect($apprenants);
        $activeCount = $learnersCollection->filter(function ($learner) {
            $statut = is_object($learner->statut ?? null) ? $learner->statut->value : ($learner->statut ?? null);
            return in_array($statut, ['PROCESSING', 'processing', 'Processing'], true);
        })->count();
    @endphp
    <section class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3" aria-label="Statistiques des apprenants">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <p class="text-xs text-slate-500">Apprenants actifs (page actuelle)</p>
            <p class="mt-2 font-serif text-3xl font-bold text-atelier-ink">{{ $activeCount }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <p class="text-xs text-slate-500">Total affiché</p>
            <p class="mt-2 font-serif text-3xl font-bold text-atelier-ink">{{ method_exists($apprenants, 'total') ? $apprenants->total() : $learnersCollection->count() }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <p class="text-xs text-slate-500">Page</p>
            <p class="mt-2 font-serif text-3xl font-bold text-atelier-ink">{{ method_exists($apprenants, 'currentPage') ? $apprenants->currentPage() : 1 }}</p>
        </article>
    </section>

    <section class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-center" aria-label="Recherche et filtres">
        <div class="flex flex-wrap gap-3">
        <form method="GET" action="{{ route('apprenant.index') }}"
            class="flex flex-wrap items-center gap-3">

            {{-- Recherche --}}
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Rechercher un apprenant..."
                class="min-w-[220px] rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500"
            >

            {{-- Filtre par statut --}}
            <select
                id="statutFilter"
                name="statut"
                onchange="this.form.submit()"
                class="min-w-[170px] rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-blue-500"
                aria-label="Filtrer par statut">

                <option value="all" {{ request('statut', 'all') == 'all' ? 'selected' : '' }}>
                    Tous les statuts
                </option>

                @foreach (\App\Enum\ApprenantStatut::cases() as $statutCase)
                    <option
                        value="{{ $statutCase->value }}"
                        {{ request('statut') == $statutCase->value ? 'selected' : '' }}>
                        {{ ucfirst(strtolower($statutCase->name)) }}
                    </option>
                @endforeach

            </select>

            {{-- Bouton rechercher --}}
            <button type="submit"
                    class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-medium text-white hover:bg-blue-700">
                Rechercher
            </button>

            {{-- Réinitialiser --}}
            <a href="{{ route('apprenant.index') }}"
            class="rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-600 hover:bg-slate-50">
                Réinitialiser
            </a>

        </form>
</div>
        <p id="resultCount" class="text-right text-xs text-slate-400 lg:ml-auto">{{ $learnersCollection->count() }} résultat(s) sur cette page</p>
    </section>

    <section class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] border-collapse text-left text-sm" id="learnersTable">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-[.13em] text-slate-500"><tr>
                    <th class="w-px whitespace-nowrap px-4 py-4 font-semibold">Matricule</th>
                    <th class="px-4 py-4 font-semibold">Apprenant</th>
                    <th class="px-4 py-4 font-semibold">Email</th>
                    <th class="px-4 py-4 font-semibold">Téléphone</th>
                    <th class="px-4 py-4 font-semibold">Formations choisies</th> <!-- Corrigé -->        
                    <th class="px-4 py-4 font-semibold">Date de naissance</th>
                    <th class="px-4 py-4 font-semibold">Vacation choisie</th>
                    <th class="px-4 py-4 font-semibold">Statut</th>
                    <th class="px-4 py-4 text-center font-semibold">Actions</th>
                </tr>
            </thead>
                <tbody id="learnersBody" class="divide-y divide-slate-100">
                    @forelse ($apprenants as $apprenant)
                   @php
    // 1. Récupération de la valeur brute du statut
    $rawstatut = is_object($apprenant->statut ?? null) 
        ? $apprenant->statut->value 
        : ($apprenant->statut ?? '');

    // 2. Conversion en minuscules pour garantir la correspondance avec le match
    $statutValue = strtolower(trim($rawstatut));

    $statutConfig = match ($statutValue) {
        'pending' => [
            'label' => 'En attente', 
            'classes' => 'bg-amber-50 text-amber-700 border border-amber-200'
        ],
        'active', 'actif', 'en_cours', 'en cours' => [
            'label' => 'En cours', 
            'classes' => 'bg-emerald-50 text-emerald-700 border border-emerald-200'
        ],
        'archived', 'archive', 'archivé' => [
            'label' => 'Archivé', 
            'classes' => 'bg-rose-50 text-rose-700 border border-rose-200'
        ],
        default => [
            'label' => $rawstatut ?: 'Non défini', 
            'classes' => 'bg-sky-50 text-sky-700 border border-sky-200'
        ],
    };

    $apprenantId = $apprenant->id_apprenant;
@endphp

                        <tr class="learner-row transition hover:bg-slate-50" data-name="{{ \Illuminate\Support\Str::lower($apprenant->nom.' '.$apprenant->prenoms) }}" data-statut="{{ $statutValue }}">
                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-blue-700">{{ $apprenant->matricule ?? '—' }}</td>
                            <td class="px-7 py-4"><p class="font-semibold text-slate-800">
                                {{ $apprenant->nom }} 
                                {{ $apprenant->prenoms }}</p>
                                <p class="mt-1 text-xs text-slate-400">Apprenant</p></td>
                            <td class="px-4 py-4 text-xs text-slate-500">{{ $apprenant->email ?? 'Non renseigné' }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-600">{{ $apprenant->telephone ?? 'Non renseigné' }}</td>

                            {{-- 2. Affichage des formations (Many-to-Many) --}}
                        <td class="px-4 py-4 text-xs text-slate-800">
                                <div class="flex flex-col gap-1">
                                    @forelse($apprenant->formations as $formation)
                                        <span class="inline-block w-fit bg-slate-100 rounded px-2.5 py-1 text-slate-700">
                                            {{ $formation->intitule }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 italic">Aucune formation</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-500">{{ $apprenant->date_naissance ? \Carbon\Carbon::parse($apprenant->date_naissance)->format('d/m/Y') : 'N/A' }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-500">{{ $apprenant->vacations->libelle ?? $apprenant->vacations->intitule ?? 'Non spécifiée' }}</td>
                            <td class="px-4 py-4"><span class="inline-flex items-center gap-2
                                 whitespace-nowrap rounded-full px-3 py-1 text-[11px] font-semibold {{ $statutConfig['classes'] }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current">
                                </span>{{ $statutConfig['label'] }}
                            </span>
                        </td>
                            <td class="px-4 py-3"><div class="flex items-center justify-center gap-2">
                                @if (\Illuminate\Support\Facades\Route::has('apprenant.show'))
                                    <a href="{{ route('apprenant.show', $apprenantId) }}"
                                     class="rounded-lg border border-slate-200 p-2 text-slate-500 transition
                                      hover:border-blue-300 hover:text-blue-600" title="Voir les détails" 
                                      aria-label="Voir {{ $apprenant->nom }}">
                                        <i data-lucide="eye" class="h-4 w-4"></i></a>
                                @endif
                                
                                <a href="{{ route('apprenant.paiement.index', $apprenantId) }}"
                                   class="rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:border-emerald-300 hover:text-emerald-600"
                                   title="Paiements" aria-label="Paiements de {{ $apprenant->nom }}">
                                    <i data-lucide="banknote" class="h-4 w-4"></i>
                                </a>

                                <a href="{{ route('apprenant.edit', $apprenantId) }}"
                                   class="rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:border-blue-300 hover:text-blue-600"
                                   title="Modifier" aria-label="Modifier {{ $apprenant->nom }}">
                                    <i data-lucide="pencil" class="h-4 w-4"></i>
                                </a>

                                <button type="button" 
                                        class="open-archive-modal rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:border-rose-300 hover:text-rose-600" 
                                        title="Archiver" 
                                        aria-label="Archiver {{ $apprenant->nom }}" 
                                        data-archive-url="{{ route('apprenant.archived', $apprenantId) }}" 
                                        data-learner-name="{{ $apprenant->nom }} {{ $apprenant->prenoms }}">
                                    <i data-lucide="archive" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center"><div class="flex flex-col items-center justify-center gap-3">
                                <i data-lucide="users" class="h-10 w-10 text-slate-300"></i>
                            <p class="font-semibold text-slate-700">Aucun apprenant enregistré</p>
                            <p class="text-xs text-slate-400">Commence par ajouter un nouvel apprenant.</p>
                            <a href="{{ route('apprenant.create') }}" class="mt-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">+ Nouvel apprenant</a>
                        </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (isset($apprenants) && method_exists($apprenants, 'links'))
            <div class="flex flex-col gap-3 border-t border-slate-100 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between"><p>Affichage des apprenants enregistrés</p><div>{{ $apprenants->links() }}</div></div>
        @endif
    </section>

    {{-- Modale de confirmation d’archivage aux couleurs Rose/Rouge --}}
    <div id="archiveModal" class="hidden fixed inset-0 z-50 bg-slate-950/40 p-4" aria-hidden="true">
        <div class="flex min-h-full items-center justify-center">
            <section role="dialog" aria-modal="true" aria-labelledby="archiveTitle" class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-rose-600">Attention</p>
                        <h2 id="archiveTitle" class="mt-1 font-serif text-2xl font-bold text-atelier-ink">Archiver un apprenant</h2>
                    </div>
                    <button type="button" data-close-modal class="rounded-lg p-2 text-slate-400 hover:bg-slate-100" aria-label="Fermer"><i data-lucide="x" class="h-5 w-5"></i></button>
                </div>
                <form id="archiveForm" action="#" method="POST" class="p-6">
                    @csrf
                    @method('PUT')
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                        <p class="font-semibold">Confirmer l’archivage de : <span id="archiveApprenantName" class="font-bold text-rose-900"></span> ?</p>
                        <p class="mt-2 text-xs text-rose-700/80">Cette action modifiera le statut de l'apprenant en "Archivé". Il restera consultable mais marqué comme inactif.</p>
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <button type="button" data-close-modal class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Annuler</button>
                        <button id="submitArchiveForm" type="submit" class="rounded-xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">Confirmer l’archivage</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('archiveModal');
    const openButtons = document.querySelectorAll('.open-archive-modal');
    const closeButtons = document.querySelectorAll('[data-close-modal]');
    const archiveForm = document.getElementById('archiveForm');
    const nameSpan = document.getElementById('archiveApprenantName');

    openButtons.forEach(button => {
        button.addEventListener('click', function () {
            const url = this.getAttribute('data-archive-url');
            const name = this.getAttribute('data-learner-name');

            archiveForm.setAttribute('action', url);
            nameSpan.textContent = name;

            modal.classList.remove('hidden');
        });
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', function () {
            modal.classList.add('hidden');
        });
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal || e.target.classList.contains('flex')) {
            modal.classList.add('hidden');
        }
    });
});
</script>
@endpush