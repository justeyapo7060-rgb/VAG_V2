{{-- resources/views/formations/index.blade.php --}}
@extends('base')
@section('title', 'Formations')
@section('content')
@php
    $formationsCollection = $formations instanceof \Illuminate\Pagination\AbstractPaginator
        ? collect($formations->items()) : collect($formations ?? []);
@endphp
<section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
    <div>
        <p class="mb-3 text-[11px] font-bold uppercase tracking-[.2em] text-atelier-blue">{{ now()->translatedFormat('l d F Y') }}</p>
        <h1 class="font-serif text-4xl font-bold tracking-tight text-atelier-ink sm:text-5xl">Formations</h1>
        <p class="mt-2 text-sm text-slate-500">Gère le catalogue des formations, leur durée et leur prix.</p>
    </div>
    <div class="flex flex-wrap gap-3 xl:justify-end">
        <button type="button" id="exportBtn" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-atelier-ink shadow-sm transition hover:bg-slate-50">
            <i data-lucide="download" class="h-4 w-4"></i> Exporter CSV
        </button>
        <button type="button" class="open-formation-modal inline-flex items-center gap-2 rounded-xl bg-atelier-blue px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-blue-700"
            data-modal-mode="create" data-store-url="{{ route('formation.store') }}">
            <i data-lucide="plus" class="h-4 w-4"></i> Nouvelle formation
        </button>
    </div>
</section>
@if (session('success')) 
<div role="statut" class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }} </div> 
    @endif
@if (session('error')) 
<div role="alert" class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}
    </div> @endif
<section class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3" aria-label="Statistiques des formations">
    <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
        <p class="text-xs text-slate-500">Formations affichées</p>
        <p class="mt-2 font-serif text-3xl font-bold text-atelier-ink">{{ $formationsCollection->count() }}</p>
    </article>
    <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
        <p class="text-xs text-slate-500">Total du catalogue</p>
        <p class="mt-2 font-serif text-3xl font-bold text-atelier-ink">{{ method_exists($formations ?? null, 'total') ? $formations->total() : $formationsCollection->count() }}</p>
    </article>
    {{-- <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
        <p class="text-xs text-slate-500">Durée cumulée (formations affichées)</p>
        <p class="mt-2 font-serif text-3xl font-bold text-atelier-ink">{{ $formationsCollection->sum('duree') }}
             <span class="text-sm font-medium">mois</span>
        </p>
    </article> --}}
</section>
<section class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center" aria-label="Recherche des formations">
<form action="{{ route('formation.index') }}" method="GET">
    <label class="flex min-w-0 flex-1 items-center gap-5 rounded-xl border border-slate-300 bg-white px-3 py-3 focus-within:border-atelier-blue">
        <i data-lucide="search" class="h-4 w-4 shrink-0 text-slate-400"></i>
        <input
            id="searchInput"
            name="search"
            type="search"
            value="{{ request('search', '') }}"
            placeholder="Rechercher une formation..."
            class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400"
            aria-label="Rechercher une formation"
        >
    </label>
</form>
    
    {{-- <option value="">
    <select name="" id="">  <button id="sortBtn" type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium hover:bg-slate-50">
    </button></select>
    <select name="" id="">prix decroissant</select>
    </option> --}}
  
    <p id="resultCount" class="text-right text-xs text-slate-400">{{ $formationsCollection->count() }} résultat(s)</p>
</section>
<section class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><div class="overflow-x-auto">
<table id="formationsTable" class="w-full min-w-[800px] border-collapse text-left text-sm">
    <thead class="bg-slate-50 text-[10px] uppercase tracking-[.20em] text-slate-500">
        <tr>
            <th class="px-4 py-4">Code</th>
            <th class="px-4 py-4">Libellé</th>
            <th class="px-4 py-4">Description</th>
            <th class="px-4 py-4">Prix</th>
            <th class="px-4 py-4">Créée le</th><th class="px-4 py-4 text-center">Actions</th></tr></thead>
<tbody id="formationsBody" class="divide-y divide-slate-100">
@forelse ($formationsCollection as $formation)
<tr class="formation-row transition hover:bg-slate-50" data-name="{{ \Illuminate\Support\Str::lower($formation->code) }}">
    <td class="w-px whitespace-nowrap px-9 py-4 font-semibold  text-blue-700">{{ $formation->code }}</td>
    <td class="max-w-sm px-4 py-4 text-xs leading-5 text-slate-500">{{ $formation->intitule }}</td>
    <td class="max-w-sm px-4 py-4 text-xs leading-5 text-slate-500">{{ \Illuminate\Support\Str::limit($formation->description, 110) }}</td>
    <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-700">{{ number_format((int) $formation->prix, 0, ',', ' ') }} FCFA</td>
    <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-500">{{ $formation->created_at?->format('d/m/Y') ?? '—' }}</td>
    <td class="px-4 py-3"><div class="flex items-center justify-center gap-2">
        <button type="button" title="Modifier" aria-label="Modifier {{ $formation->code }}"
            class="open-formation-modal rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:border-blue-300 hover:text-blue-600"
            data-modal-mode="edit" data-update-url="{{ route('formation.update', $formation->id_formation) }}"
            data-intitule="{{ $formation->intitule }}" data-description="{{ $formation->description }}"
            data-prix="{{ $formation->prix }}">
            <i data-lucide="pencil" class="h-4 w-4"></i>
        </button>
        {{-- <button type="button" title="Supprimer" aria-label="Supprimer {{ $formation->intitule }}"
            class="open-formation-modal rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:border-rose-300 hover:text-rose-600"
            data-modal-mode="delete" data-delete-url="{{ route('formation.destroy', $formation->id_formation) }}"
            data-formation-name="{{ $formation->intitule }}">
            <i data-lucide="trash-2" class="h-4 w-4"></i>
        </button> --}}
        
        <a
            href="{{ route('formation.show', $formation->id_formation) }}"
            title="Afficher les apprenants"
            aria-label="Afficher les apprenants de {{ $formation->intitule }}"
            class="inline-flex items-center rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:border-blue-300 hover:text-blue-600"
        >
            <i data-lucide="eye" class="h-4 w-4"></i>
        </a>

    </div></td>
</tr>
@empty
<tr>
    <td colspan="6" class="px-4 py-12 text-center"><div class="flex flex-col items-center justify-center gap-3"><i data-lucide="book-open" class="h-10 w-10 text-slate-300"></i><p class="font-semibold text-slate-700">Aucune formation enregistrée</p>
    <p class="text-xs text-slate-400">Commence par ajouter une formation au catalogue.</p>
    <button type="button" class="open-formation-modal mt-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white" data-modal-mode="create" data-store-url="{{ route('formation.store') }}">+ Nouvelle formation</button>
</div>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
@if (isset($formations) && method_exists($formations, 'links'))
    <div class="flex flex-col gap-3 border-t border-slate-100 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
        <p>Catalogue des formations</p><div>{{ $formations->links() }}</div>
    </div>@endif
</section>

{{-- Modale unique : création, modification et suppression. --}}
<div id="formationModal" class="hidden fixed inset-0 z-50 place-items-center overflow-y-auto bg-slate-950/40 p-4" aria-hidden="true">
    <section role="dialog" aria-modal="true" aria-labelledby="modalTitle" class="my-auto w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <p id="modalEyebrow" class="text-xs font-bold uppercase tracking-widest text-atelier-blue">Catalogue</p>
                <h2 id="modalTitle" class="mt-1 font-serif text-2xl font-bold text-atelier-ink">Nouvelle formation</h2>
                <p id="modalDescription" class="mt-1 text-sm text-slate-500">Renseigne les informations de la formation.</p>
            </div>
            <button type="button" data-close-modal class="rounded-lg p-2 text-slate-400 hover:bg-slate-100" aria-label="Fermer"><i data-lucide="x" class="h-5 w-5"></i></button>
        </div>
        <form id="formationForm" action="{{ route('formation.store') }}" method="POST" class="p-6" data-formation-form>
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="_modal_mode" id="modalModeInput" value="create">
            <input type="hidden" name="_modal_action" id="modalActionInput" value="">
            <div id="formationFields" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700 sm:col-span-2">Intitule <span class="text-rose-500">*</span>
                    <input type="text" name="intitule" value="{{ old('intitule') }}" required maxlength="255" placeholder="Ex. Pâtisserie française" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('intitule') <span class="field-error mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-medium text-slate-700 sm:col-span-2">Description <span class="text-rose-500">*</span>
                    <textarea name="description" required rows="4" placeholder="Décris le contenu de la formation..." class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ old('description') }}</textarea>
                    @error('description') <span class="field-error mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-medium text-slate-700">Prix (FCFA) <span class="text-rose-500">*</span>
                    <input type="number" name="prix" value="{{ old('prix') }}" required min="0" step="1" placeholder="Ex. 150000" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('prix') <span class="field-error mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                </label>
            </div>
            <div id="deleteFields" class="hidden rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                <p class="font-semibold">Confirmer la suppression de « <span id="deleteFormationName"></span> » ?</p>
                <p class="mt-2">Cette action supprimera la formation du catalogue.</p>
            </div>
            @if ($errors->any())
                <div id="formation-validation-errors" data-mode="{{ old('_modal_mode', 'create') }}" data-action="{{ old('_modal_action', '') }}" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                    <p class="mb-2 font-semibold">Veuillez corriger les erreurs suivantes :</p>
                    <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <button type="button" data-close-modal class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Annuler</button>
                <button id="submitFormationForm" type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">Créer la formation</button>
            </div>
        </form>
    </section>
</div>

@endsection
@push('scripts')
<script src="{{ asset('js/formations.js') }}" defer></script>
@endpush
