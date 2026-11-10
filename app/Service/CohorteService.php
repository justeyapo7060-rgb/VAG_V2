<?php

namespace App\Service;

use App\CohorteStatut;
use App\Enum\ApprenantStatut;
use App\Enum\CohorteStatut as EnumCohorteStatut;
use App\Http\Requests\CohorteRequest;
use App\Models\Apprenant;
use App\Models\Cohorte;
use App\Models\Formation;
use App\Models\Vacation;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CohorteService
{
    public const TAILLE_COHORTE = 14;
    public const TAILLE_MIN_COHORTE= 7;



    public function ajouterApprenants(Cohorte $cohorte, array $idsApprenants): Cohorte
{
    return DB::transaction(function () use ($cohorte, $idsApprenants) {

        // Verrouiller la cohorte pour éviter les ajouts simultanés.
        $cohorte = Cohorte::where(
            'id_cohorte',
            $cohorte->id_cohorte
        )->lockForUpdate()->firstOrFail();

        $cohorte->load('apprenants.formations');

        $nombreActuel = $cohorte->apprenants->count();
        $placesRestantes = self::TAILLE_COHORTE - $nombreActuel;

        if ($placesRestantes <= 0) {
            throw ValidationException::withMessages([
                'apprenants' => 'Cette cohorte contient déjà 14 apprenants.',
            ]);
        }

        $idsApprenants = array_values(array_unique($idsApprenants));

        if (count($idsApprenants) > $placesRestantes) {
            throw ValidationException::withMessages([
                'apprenants' => "Vous ne pouvez sélectionner que {$placesRestantes} apprenant(s).",
            ]);
        }

        if ($cohorte->apprenants->isEmpty()) {
            throw ValidationException::withMessages([
                'apprenants' => 'Cette cohorte ne contient aucun apprenant permettant d’identifier sa formation et sa vacation.',
            ]);
        }

        $vacations = $cohorte->apprenants
            ->pluck('vacation_id')
            ->unique()
            ->values();

        if ($vacations->count() !== 1) {
            throw ValidationException::withMessages([
                'apprenants' => 'La vacation de cette cohorte est incohérente.',
            ]);
        }

        $formationsCommunes = $cohorte->apprenants
            ->first()
            ->formations
            ->modelKeys();

        foreach ($cohorte->apprenants->skip(1) as $apprenant) {
            $formationsCommunes = array_values(array_intersect(
                $formationsCommunes,
                $apprenant->formations->modelKeys()
            ));
        }

        if (count($formationsCommunes) !== 1) {
            throw ValidationException::withMessages([
                'apprenants' => 'Impossible d’identifier une formation unique pour cette cohorte.',
            ]);
        }

        $vacationId = $vacations->first();
        $formationId = $formationsCommunes[0];

        // Vérifier à nouveau les apprenants sélectionnés côté serveur.
        $apprenants = Apprenant::query()
            ->whereIn('id_apprenant', $idsApprenants)
            ->where('statut', ApprenantStatut::PENDING->value)
            ->whereNull('cohorte_id')
            ->where('vacation_id', $vacationId)
            ->whereHas('formations', function ($query) use ($formationId) {
                $query->where('formations.id_formation', $formationId);
            })
            ->lockForUpdate()
            ->get();

        if ($apprenants->count() !== count($idsApprenants)) {
            throw ValidationException::withMessages([
                'apprenants' => 'Certains apprenants ne sont plus disponibles ou ne correspondent pas à cette cohorte.',
            ]);
        }

        Apprenant::whereIn('id_apprenant', $apprenants->modelKeys())
            ->update([
                'cohorte_id' => $cohorte->id_cohorte,
                'statut' => ApprenantStatut::PROCESSING->value,
            ]);

        return $cohorte->fresh(['apprenants.formations', 'apprenants.vacations']);
    });
}
    public function apprenantsAjoutables(Cohorte $cohorte)
{
    $cohorte->load('apprenants.formations');

    $nombreActuel = $cohorte->apprenants->count();
    $placesRestantes = self::TAILLE_COHORTE - $nombreActuel;

    if ($placesRestantes <= 0) {
        return collect();
    }

    if ($cohorte->apprenants->isEmpty()) {
        return collect();
    }

    // Tous les apprenants de la cohorte doivent avoir la même vacation.
    $vacations = $cohorte->apprenants
        ->pluck('vacation_id')
        ->unique()
        ->values();

    if ($vacations->count() !== 1) {
        throw ValidationException::withMessages([
            'apprenants' => 'Les apprenants de cette cohorte ne possèdent pas tous la même vacation.',
        ]);
    }

    // Chercher la formation commune à tous les apprenants.
    $formationsCommunes = $cohorte->apprenants
        ->first()
        ->formations
        ->modelKeys();

    foreach ($cohorte->apprenants->skip(1) as $apprenant) {
        $formationsCommunes = array_values(array_intersect(
            $formationsCommunes,
            $apprenant->formations->modelKeys()
        ));
    }

    if (count($formationsCommunes) !== 1) {
        throw ValidationException::withMessages([
            'apprenants' => 'Impossible d’identifier une formation unique pour cette cohorte.',
        ]);
    }

    $vacationId = $vacations->first();
    $formationId = $formationsCommunes[0];

    return Apprenant::query()
        ->where('statut', ApprenantStatut::PENDING->value)
        ->whereNull('cohorte_id')
        ->where('vacation_id', $vacationId)
        ->whereHas('formations', function ($query) use ($formationId) {
            $query->where('formations.id_formation', $formationId);
        })
        ->orderBy('created_at')
        ->limit($placesRestantes)
        ->get();
}

    public function apprenantsEligibles(string $formationId, string $vacationId, bool $verrouiller = false)
    {
        $query = Apprenant::where('statut', ApprenantStatut::PENDING->value)
            ->whereNull('cohorte_id')
            ->where('vacation_id', $vacationId)
            ->whereHas('formations', fn ($q) => $q->where('formations.id_formation', $formationId))
            ->orderBy('created_at')           
            ->limit(self::TAILLE_COHORTE);
        if ($verrouiller) {
            $query->lockForUpdate();          
        }
        return $query->get();
    }

public function createCohorte(array $data): array
{
    return DB::transaction(function () use ($data) {
        $apprenants = $this->apprenantsEligibles($data['id_formation'], $data['vacation_id'], true);

        if ($apprenants->count() < self::TAILLE_MIN_COHORTE) {
            throw new Exception(
                "Nombre d'apprenants insuffisant : {$apprenants->count()} sur " . self::TAILLE_MIN_COHORTE . ' requis.'
            );
        }

        $vacation  = Vacation::find($data['vacation_id']);
        $formation = Formation::find($data['id_formation']);

        $cohorte = Cohorte::create([
            'date_debut' => $data['date_debut'],
            'date_fin'   => $data['date_fin'],
            'slug'       => Str::slug("{$data['date_debut']} {$data['date_fin']} {$vacation?->libelle}"),
        ]);

        Apprenant::whereIn('id_apprenant', $apprenants->modelKeys())->update([
            'cohorte_id' => $cohorte->getKey(),
            'statut'     => ApprenantStatut::PROCESSING->value,
        ]);

        return ['cohorte' => $cohorte, 'apprenants' => $apprenants];
    });
}

    public function index(CohorteRequest $request)
{
    $today = now()->toDateString();

    $cohortes = Cohorte::query()
        ->withCount('apprenants')
        ->with(['apprenants.vacations', 'apprenants.formations'])
        ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
            $q->where('libelle', 'like', '%' . $request->search . '%')
              ->orWhere('code', 'like', '%' . $request->search . '%');
        }))
        ->when($request->statut === 'en_cours', fn ($q) => $q->where('date_debut', '<=', $today)->where('date_fin', '>=', $today))
        ->when($request->statut === 'terminee', fn ($q) => $q->where('date_fin', '<', $today))
        ->orderByDesc('date_debut')
        ->paginate(10)
        ->withQueryString();

    return view('cohorte.index', compact('cohortes', 'stats'));
}


public function updateCohorte(array $data, Cohorte $cohorte): Cohorte
{
    try {
        return DB::transaction(function () use ($cohorte, $data) {

            // La vacation et les formations ne se modifient pas via ce formulaire.
            $cohorte->update(Arr::except($data, [
                'vacation_id',
                'formations',
            ]));

            return $cohorte->refresh();
        });
    } catch (Throwable $e) {
        Log::error("Erreur lors de la mise à jour de la cohorte.", [
            'exception'  => $e,
            'id_cohorte' => $cohorte->getKey(),
        ]);

        throw new Exception("Impossible de mettre à jour la cohorte pour le moment.", 0, $e);
    }
}
}