<?php

namespace App\Rules;

use App\Support\EthiopianPhone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EthiopianPhoneRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! EthiopianPhone::isValid($value)) {
            $fail('The :attribute must be a valid Ethiopian mobile number (09XXXXXXXX, 07XXXXXXXX or +251...).');
        }
    }
}
