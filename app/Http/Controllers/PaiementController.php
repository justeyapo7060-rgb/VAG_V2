<?php

namespace App\Http\Controllers;

use App\Enum\ModePaiement;
use App\Models\Apprenant;
use App\Models\Cohorte;
use App\Models\Paiement;
use App\Service\PaiementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
// use Barryvdh\DomPDF\Facade\Pdf;
// use App\Models\Paiement;

class PaiementController extends Controller
{
    public function __construct(protected PaiementService $paiementService)
    {
    }



    public function invoice(Paiement $paiement)
    {
        $apprenant = $paiement->apprenant;

        $id = $apprenant->id_apprenant;
        $resultat = DB::table('paiements')
                    ->where('id_apprenant',$id)
                    ->sum('montant');
        return Pdf::loadView('paiement.pdf.invoice', compact('paiement', 'apprenant','resultat'))
            ->setPaper('a4')
            ->download("invoice-{$paiement->reference}.pdf");   // ou ->stream() pour l'afficher
    }

    public function impayes(Request $request)
    {
        $cohorteId = $request->query('cohorte') ?: null;
        $seulementRetard = $request->boolean('retard');

        ['lignes' => $lignes, 'resume' => $resume] = $this->paiementService->impayes($cohorteId, $seulementRetard);
        $cohortes = Cohorte::orderByDesc('date_debut')->get(['id_cohorte', 'slug']);

        return view('paiement.impayes', compact('lignes', 'resume', 'cohortes', 'cohorteId', 'seulementRetard'));
    }

    public function index(Apprenant $apprenant)
    {
        $apprenant->load(['formations', 'cohortes']);
        $paiements = $apprenant->paiements()->orderBy('numero')->get();

        return view('paiement.index', compact('apprenant', 'paiements'));
    }

    public function store(Request $request, Apprenant $apprenant)
    {
        $data = $request->validate([
            'montant' => ['required', 'integer', 'min:1'],
            'mode_paiement' => ['required', Rule::enum(ModePaiement::class)],
            'reference_transaction' => ['nullable', 'string', 'max:100'],
        ], [
            'montant.required' => 'Le montant est obligatoire.',
            'montant.integer' => 'Le montant doit être un nombre entier (FCFA).',
            'montant.min' => 'Le montant doit être supérieur à 0.',
        ]);

        $paiement = $this->paiementService->enregistrer(
            $apprenant,
            (int) $data['montant'],
            ModePaiement::from($data['mode_paiement']),
            $data['reference_transaction'] ?? null,
        );

        return redirect()
            ->route('apprenant.paiement.index', $apprenant)
            ->with('success', "Versement n°{$paiement->numero} enregistré ({$paiement->reference}).");
    }
}
