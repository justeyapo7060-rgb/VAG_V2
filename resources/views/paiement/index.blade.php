@extends('base')
@section('title', 'Paiements')
@section('content')
@php
    $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ') . ' FCFA';
    $limite = $apprenant->date_limite;
    $enRetard = $apprenant->est_en_retard;
@endphp
<section class="mx-auto max-w-4xl space-y-6">
    <a href="{{ route('apprenant.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Retour aux apprenants
    </a>

    <div>
        <p class="text-xs font-bold uppercase tracking-widest text-atelier-blue">{{ $apprenant->matricule }}</p>
        <h1 class="mt-1 font-serif text-3xl font-bold text-atelier-ink">Paiements — {{ $apprenant->nom }} {{ $apprenant->prenoms }}</h1>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm"><p class="text-xs text-slate-500">Total des formations</p>
            <p class="mt-2 font-semibold text-slate-800">{{ $fmt($apprenant->montant_total) }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <p class="text-xs text-slate-500">Déjà payé</p><p class="mt-2 font-semibold text-emerald-700">{{ $fmt($apprenant->total_paye) }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <p class="text-xs text-slate-500">Reste à payer</p>
            <p class="mt-2 font-semibold {{ $apprenant->reste_a_payer > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                {{ $fmt($apprenant->reste_a_payer) }}
            </p>
            </article>
        <article class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
            <p class="text-xs text-slate-500">Date limite (fin de cohorte)</p>
            <p class="mt-2 font-semibold {{ $enRetard ? 'text-rose-700' : 'text-slate-800' }}">{{ $limite ? $limite->format('d/m/Y') : 'Cohorte non attribuée' }}</p>
            @if ($enRetard) <p class="mt-1 text-xs font-semibold text-rose-600">En retard</p> @endif
        </article>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-left text-sm">
            <thead class="bg-slate-50 text-[10px] uppercase tracking-[.13em] text-slate-500">
                <tr><th class="px-4 py-3">N°</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Montant</th><th class="px-4 py-3">Mode</th><th class="px-4 py-3">Référence</th><th class="px-4 py-3">Statut</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($paiements as $p)
                    <tr>
                        <td class="px-4 py-3 font-semibold">{{ $p->numero }}</td>
                        <td class="px-4 py-3">{{ $p->date_paiement->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $fmt($p->montant) }}</td>
                        <td class="px-4 py-3">{{ $p->mode_paiement->label() }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $p->reference }}</td>
                        <td class="px-4 py-3">{{ $p->statut->label() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Aucun versement enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($apprenant->est_solde)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">Formation entièrement soldée.</div>
    @elseif ($apprenant->reste_a_payer > 0)
        @php $dernier = $apprenant->nombre_versements + 1 >= \App\Service\PaiementService::MAX_VERSEMENTS; @endphp
        <form method="POST" action="{{ route('apprenant.paiement.store', $apprenant) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <h2 class="text-lg font-semibold text-slate-800">Versement n°{{ $apprenant->nombre_versements + 1 }} sur {{ \App\Service\PaiementService::MAX_VERSEMENTS }}</h2>
            @if ($dernier)
                <p class="mt-1 text-sm text-amber-700">Dernier versement : il doit solder le reste ({{ $fmt($apprenant->reste_a_payer) }}).</p>
            @endif
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <label class="block text-sm font-medium text-slate-700">Montant (FCFA)
                    <input type="number" name="montant" min="1" max="{{ $apprenant->reste_a_payer }}" step="1" required
                           value="{{ old('montant', $dernier ? $apprenant->reste_a_payer : '') }}"
                           class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('montant') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-medium text-slate-700">Mode
                    <select name="mode_paiement" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 font-normal outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @foreach (\App\Enum\ModePaiement::cases() as $mode)
                            <option value="{{ $mode->value }}" @selected(old('mode_paiement') === $mode->value)>{{ $mode->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm font-medium text-slate-700">Réf. transaction (optionnel)
                    <input type="text" name="reference_transaction" maxlength="100" value="{{ old('reference_transaction') }}"
                           class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-3 font-normal outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </label>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Enregistrer le versement</button>
                <a href="{{ route('paiement.invoice', $p) }}"
                class="inline-flex items-center justify-center gap-4 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-blue-100">

                <i data-lucide="file-down" class="h-4 w-4"></i>

                <span>Reçu PDF</span>

                <i data-lucide="arrow-down-to-line" class="h-4 w-4"></i>
            </a>
            </div>

               
        </form>
    @endif
</section>
@endsection
