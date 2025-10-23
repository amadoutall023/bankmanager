<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Pas d'authentification requise
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'sometimes|in:epargne,cheque',
            'balance' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:active,inactive,closed'
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Le type doit être epargne ou cheque',
            'balance.numeric' => 'Le solde doit être un nombre',
            'balance.min' => 'Le solde ne peut pas être négatif',
            'status.in' => 'Le statut doit être actif, inactif ou fermé'
        ];
    }
}
