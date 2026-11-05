@extends('base')

@section('title', 'Ajouter des apprenants')

@section('content')

@php
    $input = 'mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100';
@endphp

<section class="mx-auto max-w-4xl space-y-6">

    <a href="{{ route('cohorte.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 transition hover:text-blue-600">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Retour aux cohortes
    </a>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <p class="text-[11px] font-bold uppercase tracking-[.15em] text-blue-600">
                    Gestion des apprenants
                </p>

                <h1 class="mt-2 text-2xl font-bold text-slate-800">
                    Ajouter des apprenants
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Complétez votre cohorte sans dépasser sa capacité maximale.
                </p>
            </div>

            <div class="rounded-xl bg-blue-50 px-5 py-3 text-center">
                <p class="text-xs text-blue-600">Places restantes</p>
                <p class="text-2xl font-bold text-blue-700">
                    {{ $placesRestantes }}
                </p>
            </div>

        </div>

        @if (session('error'))
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Effectif actuel</p>
                <p class="mt-1 text-xl font-bold text-slate-800">
                    {{ $nombreActuel }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Capacité maximale</p>
                <p class="mt-1 text-xl font-bold text-slate-800">14</p>
            </div>

            <div class="col-span-2 rounded-xl border border-slate-100 bg-slate-50 p-4 sm:col-span-1">
                <p class="text-xs text-slate-500">Apprenants disponibles</p>
                <p class="mt-1 text-xl font-bold text-slate-800">
                    {{ $apprenants->count() }}
                </p>
            </div>

        </div>

        @if ($apprenants->isNotEmpty())

            <form action="{{ route('cohorte.apprenant.store', $cohorte->id_cohorte) }}"
                  method="POST"
                  class="mt-6">

                @csrf

                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-800">
                            Apprenants disponibles
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Sélectionnez au maximum {{ $placesRestantes }} apprenant(s).
                        </p>
                    </div>

                    <span class="text-xs font-semibold text-blue-700">
                        Sélectionnés :
                        <span id="selectionCount">0</span>/{{ $placesRestantes }}
                    </span>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200">

                    <div class="divide-y divide-slate-100">

                        @foreach ($apprenants as $apprenant)

                            <label class="flex cursor-pointer items-center gap-4 px-4 py-4 transition hover:bg-slate-50">

                                <input
                                    type="checkbox"
                                    name="apprenants[]"
                                    value="{{ $apprenant->id_apprenant }}"
                                    class="apprenant-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    @checked(in_array($apprenant->id_apprenant, old('apprenants', [])))
                                >

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ $apprenant->nom }} {{ $apprenant->prenoms }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Matricule :
                                        {{ $apprenant->matricule ?? 'Non renseigné' }}
                                    </p>
                                </div>

                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-semibold text-emerald-700">
                                    Disponible
                                </span>

                            </label>

                        @endforeach

                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse justify-end gap-3 sm:flex-row">

                    <a href="{{ route('cohorte.index') }}"
                       class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Annuler
                    </a>

                    <button
                        type="submit"
                        id="submitButton"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        disabled>
                        <i data-lucide="user-plus" class="h-4 w-4"></i>
                        Ajouter les apprenants
                    </button>

                </div>

            </form>

        @else

            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5 text-center">
                <i data-lucide="users" class="mx-auto h-8 w-8 text-amber-600"></i>

                <p class="mt-3 font-semibold text-amber-800">
                    Aucun apprenant disponible
                </p>

                <p class="mt-1 text-xs text-amber-700">
                    Aucun apprenant en attente ne correspond à la formation et à la vacation de cette cohorte.
                </p>
            </div>

            <div class="mt-5 flex justify-end">
                <a href="{{ route('cohorte.index') }}"
                   class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Retour
                </a>
            </div>

        @endif

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.apprenant-checkbox');
    const compteur = document.getElementById('selectionCount');
    const bouton = document.getElementById('submitButton');
    const limite = {{ $placesRestantes }};

    function actualiserSelection() {
        const selectionnes = document.querySelectorAll(
            '.apprenant-checkbox:checked'
        );

        compteur.textContent = selectionnes.length;
        bouton.disabled = selectionnes.length === 0;

        checkboxes.forEach(function (checkbox) {
            checkbox.disabled =
                !checkbox.checked && selectionnes.length >= limite;
        });
    }

    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', actualiserSelection);
    });

    actualiserSelection();
});
</script>

@endsection
