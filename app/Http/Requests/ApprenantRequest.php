<?php

namespace App\Http\Requests;

use App\Enum\ApprenantStatut;
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
            'date_naissance' => ['required', 'date', 'before_or_equal:today'],
            'sexe'           => ['required', 'in:M,F,Masculin,Féminin'], // <-- Modifié pour accepter les formats Seeder et Formulaire
            'nationalite'    => ['nullable', 'string', 'max:100'],
            'formations'     => ['required', 'array', 'min:1'],
            'formations.*'   => ['exists:formations,id_formation'],
            'vacation_id'    => ['required'],
            'cohorte_id'     => ['nullable'],
        ] + ($this->isMethod('post') ? [
            // Acompte versé à l'inscription (1er des 4 versements), en FCFA.
            'acompte'        => ['required', 'integer', 'min:1'],
            'mode_paiement'  => ['nullable', Rule::enum(\App\Enum\ModePaiement::class)],
        ] : []);
    }
        
    public function messages(): array
    {
        return [
            'formations.required'     => 'Vous devez obligatoirement associer au moins une formation à cette session.',
            'formations.array'        => 'Le format des formations sélectionnées est invalide.',
            'formations.min'          => 'Veuillez cocher au moins une formation.',
            'formations.*.exists'     => 'L\'une des formations sélectionnées est introuvable en base de données.',
        
            'acompte.required' => 'L’acompte à l’inscription est obligatoire.',
            'acompte.integer'  => 'L’acompte doit être un nombre entier (FCFA).',
            'acompte.min'      => 'L’acompte doit être supérieur à 0.',

            'nom.required' => 'Le nom est obligatoire.',
            'nom.string'   => 'Le nom doit être une chaîne de caractères.',
            'nom.max'      => 'Le nom ne doit pas dépasser 100 caractères.',

            'prenoms.required' => 'Le prénom est obligatoire.',
            'prenoms.string'   => 'Le prénom doit être une chaîne de caractères.',
            'prenoms.max'      => 'Le prénom ne doit pas dépasser 150 caractères.',

            'sexe.required' => 'Le sexe est obligatoire.',
            'sexe.in'       => 'Le sexe sélectionné est invalide. Choisissez M, F, Masculin ou Féminin.',

            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.email'    => 'Veuillez saisir une adresse e-mail valide.',
            'email.unique'   => 'Cette adresse e-mail est déjà utilisée par un autre apprenant.',

            'telephone.max'  => 'Le numéro de téléphone ne doit pas dépasser 30 caractères.',

            'date_naissance.required'        => 'La date de naissance est obligatoire.',
            'date_naissance.date'            => 'Veuillez saisir une date de naissance valide.',
            'date_naissance.before_or_equal' => 'La date de naissance ne peut pas être dans le futur.',

            'nationalite.string'   => 'La nationalité doit être une chaîne de caractères.',
            'nationalite.max'      => 'La nationalité ne doit pas dépasser 100 caractères.',
        ];
    }
}
