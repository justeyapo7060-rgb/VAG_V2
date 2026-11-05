@extends('base')
@section('title', 'Modifier la cohorte')

@section('content')
@php
    $input = 'mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 font-normal outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100';
@endphp

<section class="mx-auto max-w-3xl space-y-6">
    <a href="{{ route('cohorte.show', $cohorte) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Retour à la cohorte
    </a>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-3xl font-bold text-slate-800">Modifier la cohorte</h1>
        <p class="mt-2 text-sm text-slate-500">Mettez à jour le libellé, le code ou les dates.</p>

        @if (session('error'))
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('cohorte.update', $cohorte) }}" method="POST" class="mt-6 space-y-4">
            @csrf
            @method('PUT')
{{-- 
            <label class="block text-sm font-medium text-slate-700">Libellé de la cohorte
                <input type="text" name="libelle" value="{{ old('libelle', $cohorte->libelle) }}" required maxlength="255" class="{{ $input }}">
                @error('libelle') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
            </label>

            <label class="block text-sm font-medium text-slate-700">Code de la cohorte
                <input type="text" name="code" value="{{ old('code', $cohorte->code) }}" required maxlength="50" class="{{ $input }}">
                @error('code') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
            </label> --}}

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">Date de début
                    <input type="date" name="date_debut" value="{{ old('date_debut', \Carbon\Carbon::parse($cohorte->date_debut)->format('Y-m-d')) }}" required class="{{ $input }}">
                    @error('date_debut') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-medium text-slate-700">Date de fin
                    <input type="date" name="date_fin" value="{{ old('date_fin', \Carbon\Carbon::parse($cohorte->date_fin)->format('Y-m-d')) }}" required class="{{ $input }}">
                    @error('date_fin') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('cohorte.show', $cohorte) }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Annuler</a>
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</section>
@endsection