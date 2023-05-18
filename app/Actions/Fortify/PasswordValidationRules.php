<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Rules\Password;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', new Password, 'confirmed'];
    }
}
