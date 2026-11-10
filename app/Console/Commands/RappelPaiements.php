<?php

namespace App\Console\Commands;

use App\Enum\ApprenantStatut;
use App\Models\Apprenant;
use App\Notifications\RappelPaiement;
use Illuminate\Console\Command;

class RappelPaiements extends Command
{
    protected $signature = 'paiements:rappel {--dry-run : Liste les apprenants concernés sans envoyer d\'e-mail}';

    protected $description = 'Envoie un rappel de paiement aux apprenants qui n\'ont pas soldé leurs formations';

    public function handle(): int
    {
        $envoyes = 0;

        Apprenant::query()
            ->where('statut', '!=', ApprenantStatut::ARCHIVED->value)
            ->with(['formations', 'cohortes'])
            ->each(function (Apprenant $apprenant) use (&$envoyes) {
                if ($apprenant->reste_a_payer <= 0 || $apprenant->montant_total <= 0) {
                    return;
                }

                if ($this->option('dry-run')) {
                    $this->line("[dry-run] {$apprenant->matricule} — reste {$apprenant->reste_a_payer} FCFA");
                } else {
                    $apprenant->notify(RappelPaiement::pour($apprenant));
                }
                $envoyes++;
            });

        $this->info("{$envoyes} rappel(s) " . ($this->option('dry-run') ? 'à envoyer.' : 'envoyé(s).'));

        return self::SUCCESS;
    }
}
