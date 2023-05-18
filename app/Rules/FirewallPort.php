<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class FirewallPort implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_numeric($value)) {
            if ($value < 1 || $value > 65535) {
                $fail(__('The :attribute field must be between :min and :max.', ['min' => 1, 'max' => 65535]));
            }

            return;
        }

        if (Str::substrCount($value, ':') !== 1) {
            $fail(__('The :attribute field is invalid.'));

            return;
        }

        [$fromPort, $toPort] = explode(':', $value);

        if ($toPort < $fromPort) {
            $fail(__('The range is invalid.'));
        }

        if ($toPort < 1 || $toPort > 65535) {
            $fail(__('The :attribute field must be between :min and :max.', ['min' => 1, 'max' => 65535]));
        }

        if ($fromPort < 1 || $fromPort > 65535) {
            $fail(__('The :attribute field must be between :min and :max.', ['min' => 1, 'max' => 65535]));
        }
    }
}
