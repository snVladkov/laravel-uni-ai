<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GradeStep implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            return;
        }

        $doubled = round(((float) $value) * 2, 4);

        if (abs($doubled - round($doubled)) > 0.0001) {
            $fail(__('validation.grade_step'));
        }
    }
}
