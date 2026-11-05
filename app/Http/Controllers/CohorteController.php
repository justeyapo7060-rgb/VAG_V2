<?php

namespace App\Http\Controllers;

use App\Enum\Apprenantstatut;
use App\Http\Requests\CohorteRequest;
use App\Models\Cohorte;
use App\Models\Formation;
use App\Models\Vacation;
use App\Service\CohorteService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class CohorteController extends Controller
{
    public function __construct(private CohorteService $cohorte_service) {}

    public function index()
    {
        $cohortes = Cohorte::withCount('apprenants')
        ->with([
            'apprenants.vacations',
            'apprenants.formations',
        ])
        ->paginate(10)
        ->withQueryString();        
        return view('cohorte.index', compact('cohortes'));
        }
    public function create(Request $request)
    {
        // $tailleCohorte = CohorteService::TAILLE_COHORTE;
        $filtres = $request->validate([
            'id_formation' => ['nullable', 'exists:formations,id_formation'],
            'vacation_id'  => ['nullable', 'exists:vacations,id_vacation'],
        ]);

        $formationId = $filtres['id_formation'] ?? null;
        $vacationId  = $filtres['vacation_id'] ?? null;

        $apprenants = ($formationId && $vacationId)
            ? $this->cohorte_service->apprenantsEligibles($formationId, $vacationId)
            : null;

        return view('cohorte.create', [
            'formations'    => Formation::orderBy('created_at')->get(),
            'vacations'     => Vacation::orderBy('libelle')->get(),
            'formationId'   => $formationId,
            'vacationId'    => $vacationId,
            'apprenants'    => $apprenants,
        ]);
    }

    public function store(CohorteRequest $request)
    {
        $data = $request->validated();

        try {
            $resultat = $this->cohorte_service->createCohorte($data);
        } catch (Exception $e) {
            Log::error('Création de cohorte impossible : ' . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('cohorte.index')->with(
            'success',
            "Cohorte « {$resultat['cohorte']->libelle} » créée avec {$resultat['apprenants']->count()} apprenants."
        );
    }

    public function show(Request $request, Cohorte $cohorte)
    {
        $cohorte->load(['apprenants.vacations', 'apprenants.formations']);

        $tous = $cohorte->apprenants->sortBy(fn ($a) => $a->nom . ' ' . $a->prenoms);

        $presents = $tous->filter(fn ($a) => $a->statut === Apprenantstatut::PROCESSING);

        // ?tous=1 affiche tous les apprenants de la cohorte, sinon seulement les présents
        $apprenants = $request->boolean('tous') ? $tous : $presents;

        return view('cohorte.show', [
            'cohorte'        => $cohorte,
            'apprenants'     => $apprenants,
            'totalApprenants' => $tous->count(),
            'totalPresents'  => $presents->count(),
        ]);
    }

    public function edit(Cohorte $cohorte)
    {
        return view('cohorte.edit', compact('cohorte'));
    }

    public function update(CohorteRequest $request, Cohorte $cohorte)
    {
        try {
            $this->cohorte_service->updateCohorte($request->validated(),$cohorte);
        } catch (Exception $e) {
            Log::error('Modification de cohorte impossible : ' . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('cohorte.show', $cohorte)->with('success', 'Cohorte modifiée avec succès.');
    }

    public function createApprenants(
        Cohorte $cohorte,
        CohorteService $cohorteService
    ) {
        $nombreActuel = $cohorte->apprenants()->count();
        $placesRestantes = CohorteService::TAILLE_COHORTE - $nombreActuel;

        if ($placesRestantes <= 0) {
            return redirect()
                ->route('cohorte.index')
                ->with('error', 'Cette cohorte contient déjà 14 apprenants.');
        }

        $apprenants = $cohorteService->apprenantsAjoutables($cohorte);

        return view('cohorte.ajouter-apprenants', compact(
            'cohorte',
            'apprenants',
            'nombreActuel',
            'placesRestantes'
        ));
    }


        public function storeApprenants(
            Request $request,
            Cohorte $cohorte,
            CohorteService $cohorteService
        ) {
            $data = $request->validate([
                'apprenants' => ['required', 'array', 'min:1'],
                'apprenants.*' => [
                    'required',
                    'distinct',
                    'exists:apprenants,id_apprenant',
                ],
            ]);

            try {
                $cohorteService->ajouterApprenants(
                    $cohorte,
                    $data['apprenants']
                );

                return redirect()
                    ->route('cohorte.show', $cohorte)
                    ->with('success', 'Les apprenants ont été ajoutés à la cohorte.');
            } catch (ValidationException $e) {
                throw $e;
            } catch (Throwable $e) {
        Log::error('Erreur lors de l’ajout des apprenants à la cohorte.', [
            'id_cohorte' => $cohorte->id_cohorte,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'exception' => $e,
        ]);

    return back()
        ->withInput()
        ->with('error', $e->getMessage());
}

        }


}