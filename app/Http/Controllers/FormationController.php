<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormationRequest;
use App\Models\Formation;
use App\Service\FormationService;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function __construct(
        protected FormationService $formation_service
    ) {
    }

    public function index(Request $request)
    {

        $search = $request->input('search', '');
        $formations = $this->formation_service->getForAllParam($search);
        $count = Formation::orderBy('created_at')->count();
        return View('formation.index',compact('formations','count'));

    }

     public function create()
    {
        return view('formation.create'
        // ['formations' => Formation::orderBy('created_at')->paginate(1)]
        );
    }

    public function edit(Formation $formation) {
        return view('formation.edit',compact('formation'));
    }

    public function update(FormationRequest $request, Formation $formation)
    {
        $this->formation_service->updateFormation($request->validated(),$formation);
        return redirect()
            ->route('formation.index')
            ->with('success', 'formation modifié avec succès.');
    }

    
    public function store(FormationRequest $request) 
    {
        $this->formation_service->createNewFormation($request->validated());
        return redirect()
                ->route('formation.index')
                ->with('success', 'formation créé avec succès.');
    }
    public function show(string $id)
    {
        $formation = Formation::with('apprenants')->findOrFail($id);
        return view('formation.show', [
            'formation' => $formation,
            'apprenants' => $formation->apprenants,
        ]);
    }


    // public function archived(string $id) {
    //     $this->formation_service->ArchivedApprenanent($id);
    //     return redirect()
    //         ->route('apprenant.index')
    //         ->with('success', 'Apprenant archivé avec succès.');
    // }
}
