<?php

namespace App\Service;

use App\Models\Formation;
use Dotenv\Parser\Value;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class FormationService
{
    /**
     * Create a new class instance.
     */


public function createNewFormation(array $data): Formation
{
    try {
        return DB::transaction(function () use ($data) {
            
            if (isset($data['intitule'])) {
                $data['intitule'] = mb_convert_case(trim($data['intitule']), MB_CASE_TITLE, "UTF-8");
            }
            
            if (isset($data['description'])) {
                $data['description'] = $data['description'];
            }
            
            if (isset($data['prix'])) {
                $data['prix'] = trim($data['prix']);
            }
               if (empty($data['code'])) {
                $data['code'] = $this->generateUniqueMaricule($data['intitule']);
            }

            return Formation::create($data);
        });

    } catch (Exception $e) {
        dd($e);
        Log::error("Erreur lors de la création de la formation : " . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'data_input' => $data
        ]
        );
        throw new Exception("Impossible de créer la formation pour le moment. Veuillez réessayer.");
        }
}

public function getForAllParam(string $value) {
 return Formation::query()
        ->where(function ($query) use ($value) {
            $query->where('intitule', 'like', "%{$value}%")
                ->orWhere('code', 'like', "%{$value}%");
        })
        ->orderBy('created_at')
        ->paginate(7)
        ->withQueryString();
} 

public function updateFormation(array $data,Formation $formation): Formation
{
    try {
        return DB::transaction(function () use ($formation, $data) {
            
            if (isset($data['code'])) {
                $data['code'] = mb_strtolower(trim($data['code']));
            }

            // $this->apprenant_repository->updateApprenant($apprenant,$data);
            $formation->update($data);
            return $formation->fresh();
        });

    } catch (Exception $e) {
        dd($e);
        
        throw new Exception("Impossible de mettre à jour l'apprenant pour le moment.");
    }
}

private function generateUniqueMaricule(string $text): string
{
    // 1. Extraction des initiales (ex: "CF")
    $initials = mb_strtoupper($this->gettheCode($text));


    if($initials)
    
    // 2. Recherche de la formation qui possède le numéro le plus élevé à la fin de son code
    $lastFormation = Formation::where('code', 'like', 'VAG-%')
        ->orderByRaw('CAST(RIGHT(code, 5) AS UNSIGNED) DESC') // Extrait les 5 derniers caractères sous forme d'entier
        ->first();

    $nextNumber = 1;

    // 3. Si un code existe, on extrait son numéro final et on fait +1
    if ($lastFormation) {
        $lastNumber = (int) substr($lastFormation->code, -5);
        $nextNumber = $lastNumber + 1;
    }

    if ($nextNumber > 10000) {
        throw new \Exception("Tous les codes possibles (10000 maximum) ont été attribués.");
    }

    return 'VAG-' . $initials . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
}

function gettheCode(string $text): string 
{
    // Remplacement des caractères accentués pour éviter des anomalies dans le code
    $cleanText = \Illuminate\Support\Str::slug($text, ' ');
        return \Illuminate\Support\Str::of($cleanText)
        ->explode(' ')                 
        ->map(fn($word) => trim($word)) 
         ->slice(0, 3)
        ->filter()                      
        ->map(fn($word) => \Illuminate\Support\Str::substr($word, 0, 1)) 
        ->join('');   
    }
                     
}

