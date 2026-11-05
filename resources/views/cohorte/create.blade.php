@extends('base')
@section('title', 'Créer une cohorte')

@section('content')
@php
    $input = 'mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 font-normal outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100';
    $tailleMinCohorte = 7;
@endphp

<section class="mx-auto max-w-3xl space-y-6">

    <a href="{{ route('cohorte.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Retour aux cohortes
    </a>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-3xl font-bold text-slate-800">Créer une cohorte</h1>
        <p class="mt-2 text-sm text-slate-500">Choisissez une formation et une vacation pour rechercher les apprenants en attente.</p>

        @if (session('error'))
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Étape 1 : rechercher les apprenants --}}
        <form method="GET" action="{{ route('cohorte.create') }}" class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
            <label class="block text-sm font-medium text-slate-700">Formation
                <select name="id_formation" required class="{{ $input }}">
                    <option value="">Choisir une formation</option>
                    @foreach ($formations as $formation)
                        <option value="{{ $formation->id_formation }}" @selected((string) $formationId === (string) $formation->id_formation)>
                            {{ $formation->intitule }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-medium text-slate-700">Vacation
                <select name="vacation_id" required class="{{ $input }}">
                    <option value="">Choisir une vacation</option>
                    @foreach ($vacations as $vacation)
                        <option value="{{ $vacation->id_vacation }}" @selected((string) $vacationId === (string) $vacation->id_vacation)>
                            {{ $vacation->libelle }}
                        </option>
                    @endforeach
                </select>
            </label>

            <button type="submit" class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100">
                Rechercher
            </button>
        </form>
    </div>


 
    {{-- Résultat de la recherche --}}
    @if ($apprenants !== null)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            @if ($apprenants->count() >= $tailleMinCohorte  &&  $apprenants->count() <= 14)

                <p class="mb-3 text-sm font-semibold text-emerald-700">
                    {{ $apprenants->count() }} apprenants disponibles : la cohorte peut être créée.
                </p>
                <ul class="mb-6 grid gap-1 text-xs text-slate-600 sm:grid-cols-2">
                    @foreach ($apprenants as $apprenant)
                        <li>{{ $apprenant->matricule ?? '—' }} · {{ $apprenant->nom }} {{ $apprenant->prenoms }}</li>
                    @endforeach
                </ul>

                {{-- Étape 2 : créer la cohorte (visible seulement si le compte est bon) --}}
                <form action="{{ route('cohorte.store') }}" method="POST" class="space-y-4 border-t border-slate-100 pt-6">
                    @csrf
                    <input type="hidden" name="id_formation" value="{{ $formationId }}">
                    <input type="hidden" name="vacation_id" value="{{ $vacationId }}">

                    {{-- <label class="block text-sm font-medium text-slate-700">Libellé de la cohorte
                        <input type="text" name="libelle" value="{{ old('libelle') }}" required maxlength="255" placeholder="Ex. Cohorte Cuisine - Matin" class="{{ $input }}">
                        @error('libelle') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="block text-sm font-medium text-slate-700">Code de la cohorte
                        <input type="text" name="code" value="{{ old('code') }}" required maxlength="50" placeholder="Ex. COH-CUI-001" class="{{ $input }}">
                        @error('code') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label> --}}

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-medium text-slate-700">Date de début
                            <input type="date" name="date_debut" value="{{ old('date_debut') }}" required class="{{ $input }}">
                            @error('date_debut') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                        </label>

                        <label class="block text-sm font-medium text-slate-700">Date de fin
                            <input type="date" name="date_fin" value="{{ old('date_fin') }}" required class="{{ $input }}">
                            @error('date_fin') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('cohorte.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Annuler</a>
                        <button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Créer la cohorte</button>
                    </div>
                </form>

            @else
                <p class="text-sm font-semibold text-amber-700">
                    Nombre d’apprenants insuffisant : {{ $apprenants->count() }} sur 
                       {{ $tailleMinCohorte }}
                     requis
                    pour cette formation et cette vacation.
                </p>
            @endif

        </div>
    @endif
</section>
@endsection