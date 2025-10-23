<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAccountsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Pour l'instant, autoriser tous les utilisateurs authentifiés
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'type' => 'nullable|in:epargne,cheque',
            'statut' => 'nullable|in:active,inactive,closed',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|in:created_at,balance,account_number',
            'order' => 'nullable|in:asc,desc',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'limit.max' => 'Le nombre maximum d\'éléments par page est de 100.',
            'type.in' => 'Le type doit être soit "epargne" soit "cheque".',
            'statut.in' => 'Le statut doit être "active", "inactive" ou "closed".',
            'sort.in' => 'Le tri doit être par "created_at", "balance" ou "account_number".',
            'order.in' => 'L\'ordre doit être "asc" ou "desc".',
        ];
    }
}
