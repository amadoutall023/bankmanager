<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SenegalesePhoneRule;
use App\Rules\StrongPasswordRule;

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
            // Champs optionnels mais au moins un requis
            'titulaire' => 'sometimes|string|max:255',
            'informationsClient' => 'sometimes|array',
            'informationsClient.telephone' => ['sometimes', 'string', new SenegalesePhoneRule(), 'unique:users,phone'],
            'informationsClient.email' => 'sometimes|email|unique:users,email',
            'informationsClient.password' => ['sometimes', 'string', new StrongPasswordRule()],
        ];
    }

    /**
     * Validation personnalisée pour s'assurer qu'au moins un champ est fourni
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            // Vérifier si au moins un champ de modification est fourni
            $hasTitulaire = isset($data['titulaire']);
            $hasTelephone = isset($data['informationsClient']['telephone']);
            $hasEmail = isset($data['informationsClient']['email']);
            $hasPassword = isset($data['informationsClient']['password']);

            if (!$hasTitulaire && !$hasTelephone && !$hasEmail && !$hasPassword) {
                $validator->errors()->add('general', 'Au moins un champ de modification doit être fourni.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'titulaire.string' => 'Le nom du titulaire doit être une chaîne de caractères',
            'titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères',
            'informationsClient.telephone.string' => 'Le téléphone doit être une chaîne de caractères',
            'informationsClient.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'informationsClient.email.email' => 'L\'email doit être valide',
            'informationsClient.email.unique' => 'Cet email est déjà utilisé',
            'informationsClient.password.string' => 'Le mot de passe doit être une chaîne de caractères',
            'general' => 'Au moins un champ de modification doit être fourni'
        ];
    }
}
