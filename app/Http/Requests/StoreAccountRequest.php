<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SenegalesePhoneRule;

class StoreAccountRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Traiter client.id = 0 comme null (nouveau client)
        if ($this->input('client.id') === 0) {
            $this->merge([
                'client' => array_merge($this->input('client', []), ['id' => null])
            ]);
        }
    }

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
            'type' => 'required|in:epargne,cheque',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|in:FCFA',
            'solde' => 'required|numeric|min:10000',
            'client' => 'required|array',
            'client.id' => 'nullable|exists:clients,id',
            'client.titulaire' => 'required_if:client.id,null|string|max:255',
            'client.email' => 'required_if:client.id,null|email|unique:users,email',
            'client.telephone' => ['required_if:client.id,null', 'string', new SenegalesePhoneRule(), 'unique:users,phone'],
            'client.adresse' => 'required_if:client.id,null|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire',
            'type.in' => 'Le type doit être epargne ou cheque',
            'soldeInitial.required' => 'Le solde initial est obligatoire',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre',
            'soldeInitial.min' => 'Le solde initial doit être supérieur ou égal à 10000',
            'devise.required' => 'La devise est obligatoire',
            'devise.in' => 'La devise doit être FCFA',
            'solde.required' => 'Le solde est obligatoire',
            'solde.numeric' => 'Le solde doit être un nombre',
            'solde.min' => 'Le solde doit être supérieur ou égal à 10000',
            'client.required' => 'Les informations du client sont obligatoires',
            'client.id.exists' => 'Le client sélectionné n\'existe pas',
            'client.titulaire.required_if' => 'Le nom du titulaire est requis',
            'client.titulaire.string' => 'Le nom du titulaire doit être une chaîne de caractères',
            'client.titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères',
            'client.email.required_if' => 'L\'email est requis',
            'client.email.email' => 'L\'email doit être valide',
            'client.email.unique' => 'Cet email est déjà utilisé',
            'client.telephone.required_if' => 'Le téléphone est requis',
            'client.telephone.string' => 'Le téléphone doit être une chaîne de caractères',
            'client.telephone.regex' => 'Le téléphone doit être un numéro sénégalais valide (+22177XXXXXXX, +22178XXXXXXX, etc.)',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'client.adresse.required_if' => 'L\'adresse est requise',
            'client.adresse.string' => 'L\'adresse doit être une chaîne de caractères',
            'client.adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères',
        ];
    }
}
