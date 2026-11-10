<?php

namespace App\Service;

use App\Enum\ApprenantStatut;
use App\Enum\ModePaiement;
use App\Enum\PaiementStatut;
use App\Models\Apprenant;
use App\Models\Paiement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaiementService
{
    /** Nombre maximal de versements (acompte inclus). */
    public const MAX_VERSEMENTS = 4;

    /**
     * Enregistre un versement libre.
     *
     * Règles : montant > 0, jamais supérieur au reste à payer,
     * et le 4e (dernier) versement doit solder exactement le reste.
     */
    public function enregistrer(
        Apprenant $apprenant,
        int $montant,
        ModePaiement $mode = ModePaiement::ESPECE,
        ?string $referenceTransaction = null,
    ): Paiement {
        return DB::transaction(function () use ($apprenant, $montant, $mode, $referenceTransaction) {
            // Recharge l'apprenant pour des calculs à jour (et évite les doubles clics).
            $apprenant = Apprenant::whereKey($apprenant->getKey())->lockForUpdate()->firstOrFail();

            $total = $apprenant->montant_total;
            $reste = $total - $apprenant->total_paye;
            $numero = $apprenant->nombre_versements + 1;

            if ($total <= 0) {
                $this->erreur('Aucune formation payante n\'est associée à cet apprenant.');
            }
            if ($reste <= 0) {
                $this->erreur('Cet apprenant a déjà soldé sa formation.');
            }
            if ($montant <= 0) {
                $this->erreur('Le montant doit être supérieur à 0.');
            }
            if ($montant > $reste) {
                $this->erreur('Le montant dépasse le reste à payer (' . number_format($reste, 0, ',', ' ') . ' FCFA).');
            }
            if ($numero >= self::MAX_VERSEMENTS && $montant !== $reste) {
                $this->erreur('Le dernier versement (n°' . self::MAX_VERSEMENTS . ') doit solder le reste : '
                    . number_format($reste, 0, ',', ' ') . ' FCFA.');
            }

            return Paiement::create([
                'id_apprenant' => $apprenant->getKey(),
                'numero' => $numero,
                'montant' => $montant,
                'date_paiement' => now(),
                'mode_paiement' => $mode,
                'reference' => $this->genererReference(),
                'reference_transaction' => $referenceTransaction,
                'statut' => PaiementStatut::VALIDE,
            ]);
        });
    }

    /**
     * Tableau de bord des impayés : apprenants non soldés (hors archivés),
     * avec résumé global. Une seule requête principale (pas de N+1).
     *
     * @return array{lignes: \Illuminate\Support\Collection, resume: array<string,int>}
     */
    public function impayes(?string $cohorteId = null, bool $seulementRetard = false): array
    {
        $query = Apprenant::query()
            ->where('statut', '!=', ApprenantStatut::ARCHIVED->value)
            ->with(['formations', 'cohortes'])
            ->withCount(['paiements as nb_versements' => fn ($q) => $q->where('statut', PaiementStatut::VALIDE->value)])
            ->withSum(['paiements as somme_payee' => fn ($q) => $q->where('statut', PaiementStatut::VALIDE->value)], 'montant');

        if ($cohorteId) {
            $query->where('cohorte_id', $cohorteId);
        }

        $lignes = $query->get()
            ->map(function (Apprenant $a) {
                $total = (int) $a->formations->sum(fn ($f) => (int) $f->prix);
                $paye = (int) $a->somme_payee;
                $limite = $a->date_limite;

                return (object) [
                    'apprenant' => $a,
                    'cohorte' => $a->cohortes?->slug,
                    'total' => $total,
                    'paye' => $paye,
                    'reste' => max(0, $total - $paye),
                    'nb_versements' => (int) $a->nb_versements,
                    'date_limite' => $limite,
                    'en_retard' => $limite !== null && $limite->copy()->endOfDay()->isPast(),
                    'jours_restants' => $limite ? (int) now()->startOfDay()->diffInDays($limite->copy()->startOfDay(), false) : null,
                ];
            })
            ->filter(fn ($l) => $l->total > 0 && $l->reste > 0);

        if ($seulementRetard) {
            $lignes = $lignes->filter(fn ($l) => $l->en_retard);
        }

        // En retard d'abord, puis échéance la plus proche (sans cohorte en dernier), puis plus gros reste.
        $lignes = $lignes->sort(fn ($a, $b) => [$b->en_retard, $a->date_limite?->timestamp ?? PHP_INT_MAX, $b->reste]
            <=> [$a->en_retard, $b->date_limite?->timestamp ?? PHP_INT_MAX, $a->reste])->values();

        $retard = $lignes->where('en_retard', true);

        return [
            'lignes' => $lignes,
            'resume' => [
                'nb_non_soldes' => $lignes->count(),
                'reste_total' => (int) $lignes->sum('reste'),
                'nb_en_retard' => $retard->count(),
                'reste_en_retard' => (int) $retard->sum('reste'),
            ],
        ];
    }

    private function genererReference(): string
    {
        do {
            $ref = 'PAY-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        } while (Paiement::where('reference', $ref)->exists());

        return $ref;
    }

    private function erreur(string $message): never
    {
        throw ValidationException::withMessages(['montant' => $message]);
    }
}
