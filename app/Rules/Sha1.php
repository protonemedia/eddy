<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

class Sha1 implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! static::passes($value)) {
            $fail(__('The SHA1 hash is invalid.'));
        }
    }

    public static function passes(string $value): bool
    {
        return (bool) preg_match('/^[a-f0-9]{40}$/i', $value);
    }
}
