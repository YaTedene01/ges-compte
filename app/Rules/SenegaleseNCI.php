<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SenegaleseNCI implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
     {
         if (!preg_match('/^[12][0-9]{12}$/', $value)) {
             $fail('Le :attribute doit être un numéro d\'identité national valide pour le Sénégal (13 chiffres commençant par 1 ou 2).');
         }
     }
}
