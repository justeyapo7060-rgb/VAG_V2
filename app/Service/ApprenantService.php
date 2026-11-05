<?php

namespace App\Service;

use App\Enum\Apprenantstatut;
use App\Models\Apprenant;
use App\Models\Formation;
use App\Models\Vacation;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use Throwable;

class ApprenantService
{
    /**
     * Create a new class instance.
     */



private function generateUniqueMaricule(): string
{
    // 1. Extraction de la date 
   $Years = date('y');
    
    // 2. Recherche de la formation qui possède le numéro le plus élevé à la fin de son code
    $lastApprenant = Apprenant::where('matricule', 'like', 'VAG-%')
        ->orderByRaw('CAST(RIGHT(matricule, 5) AS UNSIGNED) DESC') // Extrait les 5 derniers caractères sous forme d'entier
        ->first();

    $nextNumber = 1;

    // 3. Si un code existe, on extrait son numéro final et on fait +1
    if ($lastApprenant) {
        $lastNumber = (int) substr($lastApprenant->matricule, -5);
        $nextNumber = $lastNumber + 1;
    }

    if ($nextNumber > 10000) {
        throw new \Exception("Tous les codes possibles (10000 maximum) ont été attribués.");
    }

    return 'VAG-' . $Years . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
}

        public function Inscription(array $data, ?string $vacationId = null): Apprenant
        {
            return DB::transaction(function () use ($data, $vacationId) {
                $data = $this->normalizeData($data);

                $formationIds = collect(Arr::wrap($data['formations'] ?? []))
                    ->map(fn ($id) => trim((string) $id))->filter()->unique()->values();

                if ($formationIds->isEmpty()) {
                    throw new Exception('Sélectionne au moins une formation.');
                }

                $manquantes = $formationIds->diff(
                    Formation::whereIn('id_formation', $formationIds)->pluck('id_formation')
                );
                
                if ($manquantes->isNotEmpty()) {
                    throw new Exception('Formation(s) introuvable(s) : ' . $manquantes->implode(', '));
                }

                if (empty($data['statut']))    $data['statut'] = Apprenantstatut::PENDING;
                if (empty($data['matricule'])) $data['matricule'] = $this->generateUniqueMaricule();

                $vacation = $vacationId
                    ? Vacation::where('id_vacation', $vacationId)->first()
                    : null;

                if (!$vacation) {
                    throw ValidationException::withMessages([
                        'vacation_id' => 'Veuillez choisir une vacation valable.',
                    ]);
                }

                $apprenant = new Apprenant(Arr::except($data, ['formations']));       
                $apprenant->vacation_id = $vacation->id_vacation;  // écrit vacation_id, sans $fillable
                $apprenant->save();

                $apprenant->formations()->sync($formationIds->all());
                return $apprenant->load(['formations', 'vacations']);
            });
        }
        private function normalizeData(array $data): array
        {
            // Chaîne vide -> null (évite les erreurs SQL sur les colonnes date, etc.)
            foreach (['nom', 'prenoms', 'email', 'telephone', 'nationalite', 'sexe', 'date_naissance', 'vacation_id'] as $key) {
                if (array_key_exists($key, $data) && is_string($data[$key])) {
                    $trimmed = trim($data[$key]);
                    $data[$key] = $trimmed === '' ? null : $trimmed;
                }
            }

            if (!empty($data['nom']))         $data['nom']         = mb_strtoupper($data['nom'], 'UTF-8');
            if (!empty($data['prenoms']))     $data['prenoms']     = mb_convert_case($data['prenoms'], MB_CASE_TITLE, 'UTF-8');
            if (!empty($data['nationalite'])) $data['nationalite'] = mb_convert_case($data['nationalite'], MB_CASE_TITLE, 'UTF-8');
            if (!empty($data['email']))       $data['email']       = mb_strtolower($data['email'], 'UTF-8');

            return $data;
        }


    

        public function updateApprenant(array $data, Apprenant $apprenant): Apprenant
        {
            try {
                return DB::transaction(function () use ($apprenant, $data) {

            // Matricule et statut ne se modifient pas via ce formulaire.
            $apprenant->update(Arr::except($data, [
                'formations',
                'matricule',
                'statut',
                'sexe',
                'vacation_id'
            ]));

            if (isset($data['vacation_id'])) {
                $apprenant->vacation_id = $data['vacation_id'];
                $apprenant->save();
            }



                    // On ne touche au pivot que si la clé est envoyée (sinon on garde l'existant).
                    if (array_key_exists('formations', $data)) {
                        $ids = collect(Arr::wrap($data['formations']))
                            ->filter()->map(fn ($id) => (string) $id)->unique()->values()->all();
                        $apprenant->formations()->sync($ids);
                    }

                    return $apprenant->load('formations','vacations');
                });
            } catch (Throwable $e) {
                Log::error("Erreur lors de la mise à jour de l'apprenant.", [
                    'exception'    => $e,
                    'id_apprenant' => $apprenant->getKey(),
                ]);

                throw new Exception("Impossible de mettre à jour l'apprenant pour le moment.", 0, dd($e));
            }
        }



        function getCodesStepByStep()
        {
        $a = 0;
            do {
                $a++;
                $code = str_pad($a, 5 , '0', STR_PAD_LEFT);
                yield $code; 
            } while ($a <= 10000); // Boucle unique propre et continue jusqu'à 999
        }



        public function ArchivedApprenanent(string $id) {
            $apprenant = Apprenant::find($id);
            if (!$apprenant) {
            throw new Exception('cette utlisateur existe pas');
            }
            $apprenant->update(['statut'=>'archived']);
        }


        public function filterApprenant(string $statut) {
            $apprenant = Apprenant::where(['statut'=>$statut]);
            return $apprenant->get();
        }

        // public function 


}