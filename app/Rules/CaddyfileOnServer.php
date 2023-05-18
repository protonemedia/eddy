<?php

namespace App\Rules;

use App\Models\Server;
use App\Tasks\ValidateCaddyfile;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelTaskRunner\ProcessOutput;

class CaddyfileOnServer implements ValidationRule
{
    public function __construct(
        private Server $server,
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var ProcessOutput */
        $output = $this->server->runTask(new ValidateCaddyfile($value))
            ->asUser()
            ->throw()
            ->dispatch();

        if (! $output->isSuccessful() || ! Str::contains($output->getBuffer(), 'Valid configuration')) {
            $lineWithError = Collection::make($output->getLines())
                ->first(fn (string $line) => Str::contains($line, '.caddyfile:'));

            $fail(__('The Caddyfile is invalid: :error', [
                'error' => $lineWithError,
            ]));
        }
    }
}
