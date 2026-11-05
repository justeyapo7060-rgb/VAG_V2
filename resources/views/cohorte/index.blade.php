@extends('base')
@section('title', 'Cohortes')

@section('content')
    <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
        <div>
            <p class="mb-3 text-[11px] font-bold uppercase tracking-[.2em] text-atelier-blue">{{ now()->translatedFormat('l d F Y') }}</p>
            <h1 class="font-serif text-4xl font-bold tracking-tight text-atelier-ink sm:text-5xl">Cohortes</h1>
            <p class="mt-2 text-sm text-slate-500">Les groupes d’apprenants formés par formation et par vacation.</p>
        </div>
        <a href="{{ route('cohorte.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-atelier-blue px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-blue-700">
            <i data-lucide="plus" class="h-4 w-4"></i> Nouvelle cohorte
        </a>
    </section>

    @if (session('success'))
        <div role="statut" class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif
{{-- 
    <section class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3" aria-label="Statistiques des cohortes">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"><p class="text-xs text-slate-500">Cohortes au total</p><p class="mt-2 font-serif text-3xl font-bold text-atelier-ink">{{ $stats['total'] }}</p></article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"><p class="text-xs text-slate-500">En cours</p><p class="mt-2 font-serif text-3xl font-bold text-atelier-ink">{{ $stats['en_cours'] }}</p></article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"><p class="text-xs text-slate-500">À venir</p><p class="mt-2 font-serif text-3xl font-bold text-atelier-ink">{{ $stats['a_venir'] }}</p></article>
    </section> --}}

    <section class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-center" aria-label="Recherche et filtres">
        <form method="GET" action="{{ route('cohorte.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un libellé ou un code..."
                   class="min-w-[240px] rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500">
        {{-- 
            <select name="statut" onchange="this.form.submit()" aria-label="Filtrer par statut"
                    class="min-w-[170px] rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-blue-500">
                <option value="">Tous les statuts</option>
                <option value="a_venir"  @selected(request('statut') === 'a_venir')>À venir</option>
                <option value="en_cours" @selected(request('statut') === 'en_cours')>En cours</option>
                <option value="terminee" @selected(request('statut') === 'terminee')>Terminée</option>
            </select> 
            --}}

            <button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-medium text-white hover:bg-blue-700">Rechercher</button>
            <a href="{{ route('cohorte.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-600 hover:bg-slate-50">Réinitialiser</a>
        </form>
        <p class="text-right text-xs text-slate-400 lg:ml-auto">{{ $cohortes->total() }} résultat(s)</p>
    </section>

    <section class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
<table class="w-full min-w-[1050px] border-collapse text-left text-sm">
    <thead class="bg-slate-50 text-[10px] uppercase tracking-[.13em] text-slate-500">
        <tr>
            <th class="px-4 py-4 font-semibold">Formation</th>
            <th class="px-4 py-4 font-semibold">Vacation</th>
            <th class="px-4 py-4 font-semibold">Apprenants</th>
            <th class="px-4 py-4 font-semibold">Période</th>
            <th class="px-4 py-4 font-semibold">Statut</th>
            <th class="px-4 py-4 text-center font-semibold">Actions</th>
        </tr>
    </thead>

    <tbody class="divide-y divide-slate-100">
        @forelse ($cohortes as $cohorte)

            @php
                $rawstatut = is_object($cohorte->statut ?? null)
                    ? $cohorte->statut->value
                    : ($cohorte->statut ?? '');

                $statutValue = strtolower(trim($rawstatut));

                $statutConfig = match ($statutValue) {
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

                $nombreApprenants = $cohorte->apprenants_count
                    ?? $cohorte->apprenants->count();

                $placesRestantes = max(0, 14 - $nombreApprenants);
            @endphp

            <tr class="transition hover:bg-slate-50">

                {{-- Formation --}}
                <td class="whitespace-nowrap px-4 py-4 text-xs font-medium text-slate-700">
                    {{ $cohorte->formations_libelle ?: 'Non renseignée' }}
                </td>

                {{-- Vacation --}}
                <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-500">
                    {{ $cohorte->vacation_libelle ?? 'Non spécifiée' }}
                </td>

                {{-- Nombre d'apprenants --}}
                <td class="px-4 py-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-slate-700">
                            {{ $nombreApprenants }}/14
                        </span>

                        <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full {{ $nombreApprenants >= 14 ? 'bg-emerald-500' : 'bg-blue-500' }}"
                                style="width: {{ min(100, ($nombreApprenants / 14) * 100) }}%">
                            </div>
                        </div>
                    </div>

                    <p class="mt-1 text-[10px] text-slate-400">
                        @if ($placesRestantes > 0)
                            {{ $placesRestantes }} place(s) restante(s)
                        @else
                            Effectif complet
                        @endif
                    </p>
                </td>

                {{-- Période --}}
                <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-500">
                    {{ \Carbon\Carbon::parse($cohorte->date_debut)->format('d/m/Y') }}
                    →
                    {{ \Carbon\Carbon::parse($cohorte->date_fin)->format('d/m/Y') }}
                </td>

                {{-- Statut --}}
                <td class="px-4 py-4">
                    <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-[11px] font-semibold {{ $statutConfig['classes'] }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ $statutConfig['label'] }}
                    </span>
                </td>

                {{-- Actions --}}
                <td class="px-4 py-4">
                    <div class="flex items-center justify-center gap-2">

                        <a href="{{ route('cohorte.show', $cohorte) }}"
                           title="Voir les apprenants"
                           aria-label="Voir les apprenants de {{ $cohorte->libelle }}"
                           class="rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:border-blue-300 hover:text-blue-600">
                            <i data-lucide="eye" class="h-4 w-4"></i>
                        </a>

                        <a href="{{ route('cohorte.edit', $cohorte) }}"
                           title="Modifier"
                           aria-label="Modifier {{ $cohorte->libelle }}"
                           class="rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:border-blue-300 hover:text-blue-600">
                            <i data-lucide="pencil" class="h-4 w-4"></i>
                        </a>

                        {{-- Ajouter des apprenants uniquement si la cohorte n'est pas complète --}}
                        @if ($nombreApprenants < 14)
                            <a href="{{ route('cohorte.apprenant.create', $cohorte->id_cohorte) }}"
                               title="Ajouter des apprenants"
                               aria-label="Ajouter des apprenants à {{ $cohorte->libelle }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                <i data-lucide="user-plus" class="h-4 w-4"></i>
                                Ajouter
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">
                                <i data-lucide="check-circle" class="h-4 w-4"></i>
                                Complète
                            </span>
                        @endif

                    </div>
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="6" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center justify-center gap-3">
                        <i data-lucide="layers" class="h-10 w-10 text-slate-300"></i>

                        <p class="font-semibold text-slate-700">
                            Aucune cohorte trouvée
                        </p>

                        <p class="text-xs text-slate-400">
                            Créez une cohorte dès que 7 apprenants au minimum sont disponibles pour une formation et une vacation.
                        </p>

                        <a href="{{ route('cohorte.create') }}"
                           class="mt-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                            + Nouvelle cohorte
                        </a>
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
        </div>

        @if ($cohortes->hasPages())
            <div class="border-t border-slate-100 px-4 py-4 text-xs text-slate-500">{{ $cohortes->links() }}</div>
        @endif
    </section>
@endsection