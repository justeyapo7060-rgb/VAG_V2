<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FormationRequest extends FormRequest
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
            return [
            'code' => 'nullable|string|unique:formations,code',
            'intitule' => 'required|string|max:255',
            'description' => 'required|string',
            'prix' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
        {
            return [
                'code.unique'       => 'Ce code de formation est déjà attribué à une autre formation.',
                'intitule.required'  => 'Le libellé de la formation est obligatoire.',
                'intitule.max'       => 'Le libellé ne doit pas dépasser 255 caractères.',
                'description.required' => 'La description de la formation est obligatoire.',
                'prix.required'     => 'Le prix de la formation est obligatoire.',
                'prix.integer'      => 'Le prix doit être un nombre entier.',
                'prix.min'          => 'Le prix ne peut pas être négatif.',
                'duree.required'    => 'La durée de la formation est obligatoire.',
            ];
        }
}
