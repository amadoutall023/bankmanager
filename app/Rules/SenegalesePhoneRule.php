<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SenegalesePhoneRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifier le format du numéro de téléphone sénégalais
        // Format attendu: +221XXXXXXXXX où XXXXXXXXX est composé de:
        // - Indicatif régional: 77, 78, 70, 76, 75, 33
        // - 7 chiffres suivants
        if (!preg_match('/^\+221(77|78|70|76|75|33)[0-9]{7}$/', $value)) {
            $fail('Le numéro de téléphone doit être un numéro sénégalais valide (+22177XXXXXXX, +22178XXXXXXX, etc.).');
        }
    }
}
