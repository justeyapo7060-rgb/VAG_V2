<?php

namespace App\Console\Commands;

use App\Enum\Apprenantstatut;
use App\Enum\Cohortestatut;
use App\Models\Cohorte;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:terminer-cohortes')]
#[Description('Command description')]
class TerminerCohortes extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
       $cohortes = Cohorte::whereDate('date_fin', '<=', today())
            ->where('statut', '!=', Cohortestatut::COMPLETED->value)
            ->get();

        foreach ($cohortes as $cohorte) {
            DB::transaction(function () use ($cohorte) {

                // Terminer la cohorte
                $cohorte->update([
                    'statut' => Cohortestatut::COMPLETED,
                ]);

                // Terminer tous les apprenants associés
                $cohorte->apprenants()->update([
                    'statut' => Apprenantstatut::COMPLETED->value,
                ]);
            });

            $this->info("Cohorte {$cohorte->id_cohorte} terminée.");
        }

        $this->info('Traitement terminé.');

        return self::SUCCESS;
    }
}
