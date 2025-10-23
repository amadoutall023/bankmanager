<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|in:epargne,cheque',
            'balance' => 'numeric|min:0',
            'status' => 'in:active,inactive,closed'
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Le client est obligatoire',
            'client_id.exists' => 'Le client sélectionné n\'existe pas',
            'type.required' => 'Le type de compte est obligatoire',
            'type.in' => 'Le type doit être epargne ou cheque',
            'balance.numeric' => 'Le solde doit être un nombre',
            'balance.min' => 'Le solde ne peut pas être négatif',
            'status.in' => 'Le statut doit être actif, inactif ou fermé'
        ];
    }
}
