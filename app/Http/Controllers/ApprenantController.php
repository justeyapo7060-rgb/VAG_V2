<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApprenantRequest;
use App\Models\Apprenant;
use App\Models\Formation;
use App\Models\Vacation;
use App\Service\ApprenantService;
use Illuminate\Http\Request;

class ApprenantController extends Controller
{
    

    public function __construct(
        protected ApprenantService $apprenant_service  
    ) {
    }


 public function index(Request $request)
{
    $query = Apprenant::query();

    // Recherche
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('nom', 'LIKE', "%{$search}%")
                ->orWhere('prenoms', 'LIKE', "%{$search}%")
                ->orWhere('matricule', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('telephone', 'LIKE', "%{$search}%");
        });
    }

    // Filtre par statut
    if ($request->filled('status') && $request->status !== 'all') {
        $query->where('statut', $request->status);
    }

   $apprenants = $query
    ->orderBy('created_at', 'asc')
    ->paginate(10)
    ->withQueryString();

    $count = $query->count();

    $formations= Apprenant::with('formations')->get();

    return view('apprenant.index', compact('apprenants', 'count','formations'));
}
     public function create()
    {  
        $selectedFormations = collect(old('formations', isset($apprenant) ? $apprenant->formations->pluck('id_formation')->all() : []))
            ->map(fn ($id) => (string) $id)->all();
        $formations = Formation::orderBy('created_at', 'asc')->get();
        $vacations= Vacation::all();
        $apprenantCount= Apprenant::all()->count();
        $vacationsexcepsoir= Vacation::whereNot('libelle','soir')->get();
        return view('apprenant.create',compact('formations','vacations','vacationsexcepsoir','apprenantCount','selectedFormations'));
    }


    public function store(ApprenantRequest $request) {
        $apprenants = $this->apprenant_service->Inscription($request->validated(),$request->input('vacation_id'));
        return redirect()
                ->route('apprenant.index',compact('apprenants'))
                ->with('success', 'Apprenant créé avec succès.');
    }

    public function update(ApprenantRequest $request,Apprenant $apprenant) {

        $apprenantUpdate = $this->apprenant_service->updateApprenant($request->validated(),$apprenant);
        return redirect()
                ->route('apprenant.index',compact('apprenantUpdate'))
                ->with('success', 'Apprenant modifier avec succès.');

    }

    public function edit(Apprenant $apprenant) {
         $selectedFormations = collect(old('formations', isset($apprenant) ? $apprenant->formations->pluck('id_formation')->all() : []))
            ->map(fn ($id) => (string) $id)->all();
        $formations = Formation::orderBy('created_at', 'asc')->get();
        $vacations= Vacation::all();
        $apprenantCount= Apprenant::all()->count();
        $vacationsexcepsoir= Vacation::whereNot('libelle','soir')->get();
        $apprenantCount= Apprenant::all()->count();
        $formations = Formation::orderBy('created_at', 'asc')->get();
        $vacations= Vacation::all();
        return view('apprenant.edit',compact('apprenant','formations','vacations','apprenantCount','vacationsexcepsoir','selectedFormations'));
    }
        public function archived(string $id) {
        $this->apprenant_service->ArchivedApprenanent($id);
        return redirect()
            ->route('apprenant.index')
            ->with('success', 'Apprenant archivé avec succès.');
    }




    


}
