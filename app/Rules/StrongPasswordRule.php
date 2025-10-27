<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPasswordRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifier le mot de passe fort :
        // - Au moins 10 caractères
        // - Commence par une lettre majuscule
        // - Contient au moins 2 lettres minuscules
        // - Contient au moins 2 caractères spéciaux
        if (!preg_match('/^[A-Z].{9,}$/', $value)) {
            $fail('Le mot de passe doit commencer par une lettre majuscule et contenir au moins 10 caractères.');
            return;
        }

        // Compter les minuscules
        if (preg_match_all('/[a-z]/', $value) < 2) {
            $fail('Le mot de passe doit contenir au moins 2 lettres minuscules.');
            return;
        }

        // Compter les caractères spéciaux
        if (preg_match_all('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $value) < 2) {
            $fail('Le mot de passe doit contenir au moins 2 caractères spéciaux.');
            return;
        }
    }
}
