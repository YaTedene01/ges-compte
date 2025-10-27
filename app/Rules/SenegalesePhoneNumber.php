<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SenegalesePhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
     {
         if (!preg_match('/^(\+221|221)[0-9]{9}$/', $value)) {
             $fail('Le :attribute doit être un numéro de téléphone valide pour le Sénégal (format: +221XXXXXXXXX ou 221XXXXXXXXX).');
         }
     }
}
