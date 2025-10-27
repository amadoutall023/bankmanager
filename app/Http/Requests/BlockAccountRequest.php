<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlockAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Pour cette version simplifiée, on autorise tous les utilisateurs
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dureeBlocage' => 'required|integer|min:1|max:365', // Durée en jours
            'motifBlocage' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'dureeBlocage.required' => 'La durée de blocage est obligatoire',
            'dureeBlocage.integer' => 'La durée de blocage doit être un nombre entier',
            'dureeBlocage.min' => 'La durée de blocage doit être d\'au moins 1 jour',
            'dureeBlocage.max' => 'La durée de blocage ne peut pas dépasser 365 jours',
            'motifBlocage.required' => 'Le motif de blocage est obligatoire',
            'motifBlocage.string' => 'Le motif de blocage doit être une chaîne de caractères',
            'motifBlocage.max' => 'Le motif de blocage ne peut pas dépasser 500 caractères',
        ];
    }
}
