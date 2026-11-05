{{-- resources/views/apprenants/edit.blade.php --}}
@extends('base')
@section('title', 'Modifier un apprenant')
@section('content')
<section class="mx-auto max-w-3xl">
    <a href="{{ route('apprenant.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600"><i data-lucide="arrow-left" class="h-4 w-4"></i> Retour aux apprenants</a>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-5"><p class="text-xs font-bold uppercase tracking-widest text-atelier-blue">Mise à jour</p><h1 class="mt-1 font-serif text-3xl font-bold text-atelier-ink">Modifier l’apprenant</h1><p class="mt-2 text-sm text-slate-500">Modifie les informations puis enregistre les changements.</p></div>
        @if ($errors->any()) <div class="mx-6 mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><p class="mb-2 font-semibold">Veuillez corriger les erreurs suivantes :</p><ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
        <form action="{{ route('apprenant.update', $apprenant->id_apprenant) }}" method="POST" class="p-6" data-apprenant-form>
            @include('apprenant.partials.form', ['formMode' => 'edit', 'apprenant' => $apprenant])
            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end"><a href="{{ route('apprenant.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">Annuler</a><button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Enregistrer les modifications</button></div>
        </form>
    </div>
</section>
@endsection
