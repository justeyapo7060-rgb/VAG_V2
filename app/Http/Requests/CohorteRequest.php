<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CohorteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nettoie les champs avant la validation (le code est comparé sans espaces ni casse).
     */
    // protected function prepareForValidation(): void
    // {
    //     if ($this->has('code')) {
    //         $this->merge(['code' => mb_strtoupper(trim((string) $this->input('code')))]);
    //     }
    //     if ($this->has('libelle')) {
    //         $this->merge(['libelle' => trim((string) $this->input('libelle'))]);
    //     }
    // }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    $cohorte  = $this->route('cohorte');
    $id       = $cohorte instanceof \App\Models\Cohorte ? $cohorte->getKey() : $cohorte;
    $isUpdate = $cohorte !== null;

    $rules = [
        // 'libelle'    => ['required', 'string', 'max:255'],
        // 'code'       => ['required', 'string', 'max:50', Rule::unique('cohortes', 'code')->ignore($id, 'id_cohorte')],
        'date_debut' => ['required', 'date'],
        'date_fin'   => ['required', 'date', 'after_or_equal:date_debut'],
    ];

    if (! $isUpdate) {
        $rules['vacation_id'] = ['required', 'exists:vacations,id_vacation'];
    }


        if (! $isUpdate) {
        $rules['id_formation'] = ['required', 'exists:vacations,id_formation'];
    }
    return $rules;
}

    public function messages(): array
    {
        return [
            'vacation_id.required'  => 'La vacation est obligatoire.',
            'vacation_id.exists'    => 'La vacation sélectionnée est introuvable.',

            'libelle.required'      => 'Le libellé de la cohorte est obligatoire.',
            'libelle.max'           => 'Le libellé ne doit pas dépasser 255 caractères.',

            'code.required'         => 'Le code de la cohorte est obligatoire.',
            'code.max'              => 'Le code ne doit pas dépasser 50 caractères.',
            'code.unique'           => 'Ce code est déjà attribué à une autre cohorte.',

            'date_debut.required'   => 'La date de début est obligatoire.',
            'date_debut.date'       => 'La date de début n’est pas valide.',

            'date_fin.required'     => 'La date de fin est obligatoire.',
            'date_fin.date'         => 'La date de fin n’est pas valide.',
            'date_fin.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',
        ];
    }
}