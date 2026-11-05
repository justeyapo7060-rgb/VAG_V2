@extends('base')
@section('title')

@section('content')
@php
    $debut = \Carbon\Carbon::parse($cohorte->date_debut);
    $fin   = \Carbon\Carbon::parse($cohorte->date_fin);
    $afficheTous = request()->boolean('tous');
@endphp

<section class="space-y-6">
    <a href="{{ route('cohorte.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Retour aux cohortes
    </a>

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[.2em] text-atelier-blue">{{ $cohorte->code }}</p>
            <h1 class="mt-2 font-serif text-4xl font-bold tracking-tight text-atelier-ink">{{ $cohorte->libelle }}</h1>
        </div>
        <a href="{{ route('cohorte.edit', $cohorte) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-atelier-ink shadow-sm hover:bg-slate-50">
            <i data-lucide="pencil" class="h-4 w-4"></i> Modifier
        </a>
    </div>

    @if (session('success'))
        <div role="statut" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"><p class="text-xs text-slate-500">Formation</p><p class="mt-2 text-sm font-semibold text-slate-800">{{ $cohorte->formations_libelle ?: 'Non renseignée' }}</p></article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"><p class="text-xs text-slate-500">Vacation</p><p class="mt-2 text-sm font-semibold text-slate-800">{{ $cohorte->vacation_libelle ?? 'Non spécifiée' }}</p></article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"><p class="text-xs text-slate-500">Période</p><p class="mt-2 text-sm font-semibold text-slate-800">{{ $debut->format('d/m/Y') }} → {{ $fin->format('d/m/Y') }}</p></article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"><p class="text-xs text-slate-500">Apprenants présents</p><p class="mt-2 font-serif text-3xl font-bold text-atelier-ink">{{ $totalPresents }} <span class="text-base font-normal text-slate-400">/ {{ $totalApprenants }}</span></p></article>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-800">{{ $afficheTous ? 'Tous les apprenants de la cohorte' : 'Apprenants présents' }}</h2>
        @if ($afficheTous)
            <a href="{{ route('cohorte.show', $cohorte) }}" class="text-sm text-blue-600 hover:underline">Afficher seulement les présents</a>
        @else
            <a href="{{ route('cohorte.show', ['cohorte' => $cohorte, 'tous' => 1]) }}" class="text-sm text-blue-600 hover:underline">Afficher tous les apprenants</a>
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] border-collapse text-left text-sm">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-[.13em] text-slate-500">
                    <tr>
                        <th class="w-px whitespace-nowrap px-4 py-4 font-semibold">Matricule</th>
                        <th class="px-4 py-4 font-semibold">Apprenant</th>
                        <th class="px-4 py-4 font-semibold">Email</th>
                        <th class="px-4 py-4 font-semibold">Téléphone</th>
                        <th class="px-4 py-4 font-semibold">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($apprenants as $apprenant)
                        @php
                            $badge = match (strtolower($apprenant->statut?->value ?? '')) {
                                'processing', 'active', 'en_cours' => ['En cours',   'bg-emerald-50 text-emerald-700 border border-emerald-200'],
                                'pending'                          => ['En attente', 'bg-amber-50 text-amber-700 border border-amber-200'],
                                'archived'                         => ['Archivé',    'bg-rose-50 text-rose-700 border border-rose-200'],
                                default                            => [$apprenant->statut?->value ?? 'Non défini', 'bg-sky-50 text-sky-700 border border-sky-200'],
                            };
                        @endphp
                        <tr class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-blue-700">{{ $apprenant->matricule ?? '—' }}</td>
                            <td class="px-4 py-4 font-semibold text-slate-800">{{ $apprenant->nom }} {{ $apprenant->prenoms }}</td>
                            <td class="px-4 py-4 text-xs text-slate-500">{{ $apprenant->email ?? 'Non renseigné' }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-600">{{ $apprenant->telephone ?? 'Non renseigné' }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-[11px] font-semibold {{ $badge[1] }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $badge[0] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">Aucun apprenant à afficher pour cette cohorte.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection