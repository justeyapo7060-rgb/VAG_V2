@extends('base')
@section('title', 'Impayés')
@section('content')
@php $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ') . ' FCFA'; @endphp
<section class="mx-auto max-w-6xl space-y-6">
    <div>
        <p class="text-xs font-bold uppercase tracking-widest text-atelier-blue">Finances</p>
        <h1 class="mt-1 font-serif text-3xl font-bold text-atelier-ink">Suivi des impayés</h1>
        <p class="mt-1 text-sm text-slate-500">Apprenants n’ayant pas soldé leurs formations. Échéance : fin de la cohorte.</p>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"><p class="text-xs text-slate-500">Apprenants non soldés</p><p class="mt-2 text-xl font-semibold text-slate-800">{{ $resume['nb_non_soldes'] }}</p></article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"><p class="text-xs text-slate-500">Reste à encaisser</p><p class="mt-2 text-xl font-semibold text-slate-800">{{ $fmt($resume['reste_total']) }}</p></article>
        <article class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-4 shadow-sm"><p class="text-xs text-rose-700">En retard (cohorte terminée)</p><p class="mt-2 text-xl font-semibold text-rose-700">{{ $resume['nb_en_retard'] }}</p></article>
        <article class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-4 shadow-sm"><p class="text-xs text-rose-700">Montant en retard</p><p class="mt-2 text-xl font-semibold text-rose-700">{{ $fmt($resume['reste_en_retard']) }}</p></article>
    </div>

    <form method="GET" action="{{ route('paiement.impayes') }}" class="flex flex-wrap items-end gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <label class="block text-sm font-medium text-slate-700">Cohorte
            <select name="cohorte" class="mt-2 block min-w-56 rounded-xl border border-slate-200 bg-white px-3 py-2.5 font-normal outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Toutes les cohortes</option>
                @foreach ($cohortes as $c)
                    <option value="{{ $c->id_cohorte }}" @selected($cohorteId === $c->id_cohorte)>{{ $c->slug }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex items-center gap-2 pb-3 text-sm text-slate-700">
            <input type="checkbox" name="retard" value="1" @checked($seulementRetard) class="h-4 w-4 rounded border-slate-300"> Retards seulement
        </label>
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Filtrer</button>
        @if ($cohorteId || $seulementRetard)
            <a href="{{ route('paiement.impayes') }}" class="pb-3 text-sm text-slate-500 hover:text-blue-600">Réinitialiser</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-left text-sm">
            <thead class="bg-slate-50 text-[10px] uppercase tracking-[.13em] text-slate-500">
                <tr>
                    <th class="px-4 py-3">Apprenant</th><th class="px-4 py-3">Cohorte</th>
                    <th class="px-4 py-3">Payé / Total</th><th class="px-4 py-3">Reste</th>
                    <th class="px-4 py-3">Versements</th><th class="px-4 py-3">Échéance</th><th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($lignes as $l)
                    @php $pct = $l->total > 0 ? min(100, (int) round($l->paye * 100 / $l->total)) : 0; @endphp
                    <tr class="{{ $l->en_retard ? 'bg-rose-50/40' : '' }}">
                        <td class="px-4 py-3">
                            <span class="block font-semibold text-slate-800">{{ $l->apprenant->nom }} {{ $l->apprenant->prenoms }}</span>
                            <span class="text-xs text-slate-500">{{ $l->apprenant->matricule }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $l->cohorte ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="block text-xs text-slate-600">{{ $fmt($l->paye) }} / {{ $fmt($l->total) }}</span>
                            <span class="mt-1 block h-1.5 w-32 overflow-hidden rounded-full bg-slate-200"><span class="block h-full bg-emerald-500" style="width: {{ $pct }}%"></span></span>
                        </td>
                        <td class="px-4 py-3 font-semibold {{ $l->en_retard ? 'text-rose-700' : 'text-slate-800' }}">{{ $fmt($l->reste) }}</td>
                        <td class="px-4 py-3">{{ $l->nb_versements }} / {{ \App\Service\PaiementService::MAX_VERSEMENTS }}</td>
                        <td class="px-4 py-3">
                            @if (! $l->date_limite)
                                <span class="text-xs text-slate-400">Sans cohorte</span>
                            @elseif ($l->en_retard)
                                <span class="rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-semibold text-rose-700">En retard · {{ $l->date_limite->format('d/m/Y') }}</span>
                            @else
                                <span class="block">{{ $l->date_limite->format('d/m/Y') }}</span>
                                <span class="text-xs {{ $l->jours_restants <= 30 ? 'text-amber-600' : 'text-slate-500' }}">dans {{ $l->jours_restants }} j</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('apprenant.paiement.index', $l->apprenant) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:border-blue-300 hover:text-blue-600">Encaisser</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Aucun impayé pour ce filtre. 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
