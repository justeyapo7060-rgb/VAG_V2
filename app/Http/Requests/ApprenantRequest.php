<?php

namespace App\Http\Requests;

use App\Enum\Apprenantstatut;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApprenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
public function rules(): array
{
    // En modification, la route fournit l'apprenant (modèle ou identifiant selon le binding).
    $apprenant = $this->route('apprenant');
    $id = $apprenant instanceof \App\Models\Apprenant ? $apprenant->getKey() : $apprenant;

    return [
        'nom'            => ['required', 'string', 'max:100'],
        'prenoms'        => ['required', 'string', 'max:150'],
        'email'          => [
            'required', 'email', 'max:255',
            Rule::unique('apprenants', 'email')->ignore($id, 'id_apprenant'),
        ],
        'telephone'      => ['nullable', 'string', 'max:30'],
        'date_naissance' => ['required','nullable', 'date', 'before_or_equal:today'],
        'sexe'           => ['required', 'in:Masculin,Féminin'],
        'nationalite'    => ['nullable', 'string', 'max:100'],
        'formations'     => ['required', 'array', 'min:1'],
        'formations.*'   => ['exists:formations,id_formation'],
        'vacation_id'   => ['required'],
        'cohorte_id'    =>[ 'nullable'],
    ];
}
    
public function messages(): array
{
    return [

  
            'formations.required'     => 'Vous devez obligatoirement associer au moins une formation à cette session.',
            'formations.array'        => 'Le format des formations sélectionnées est invalide.',
            'formations.min'          => 'Veuillez cocher au moins une formation.',
            'formations.*.exists'     => 'L\'une des formations sélectionnées est introuvable en base de données.',
    
        'nom.required' => 'Le nom est obligatoire.',
        'nom.string'   => 'Le nom doit être une chaîne de caractères.',
        'nom.max'      => 'Le nom ne doit pas dépasser 255 caractères.',

        'prenoms.required' => 'Le prénom est obligatoire.',
        'prenoms.string'   => 'Le prénom doit être une chaîne de caractères.',
        'prenoms.max'      => 'Le prénom ne doit pas dépasser 255 caractères.',

        'sexe.required' => 'Le sexe est obligatoire.',
        'sexe.string'   => 'Le sexe doit être une chaîne de caractères.',
        'sexe.in'       => 'Le sexe sélectionné est invalide. Choisissez M, F, Masculin ou Féminin.',

        'email.required' => 'L’adresse e-mail est obligatoire.',
        'email.email'    => 'Veuillez saisir une adresse e-mail valide.',
        'email.unique'   => 'Cette adresse e-mail est déjà utilisée par un autre apprenant.',

        'telephone.required' => 'Le numéro de téléphone est obligatoire.',
        'telephone.string'   => 'Le numéro de téléphone doit être une chaîne de caractères.',
        'telephone.max'      => 'Le numéro de téléphone ne doit pas dépasser 255 caractères.',

        'date_naissance.required' => 'La date de naissance est obligatoire.',
        'date_naissance.date'     => 'Veuillez saisir une date de naissance valide.',
        'date_naissance.before'   => 'La date de naissance doit être antérieure à aujourd’hui.',

        'nationalite.required' => 'La nationalité est obligatoire.',
        'nationalite.string'   => 'La nationalité doit être une chaîne de caractères.',
        'nationalite.max'      => 'La nationalité ne doit pas dépasser 100 caractères.',
    ];
}

}
