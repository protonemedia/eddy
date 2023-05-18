<?php

namespace App\Rules;

use App\Models\Server;
use App\Tasks\ValidateMySqlConfig;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelTaskRunner\ProcessOutput;

class MySqlConfigOnServer implements ValidationRule
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
        $output = $this->server->runTask($task = new ValidateMySqlConfig($value))
            ->asUser()
            ->throw()
            ->dispatch();

        if (! $output->isSuccessful() || Str::contains($output->getBuffer(), '[ERROR]')) {
            $error = Collection::make($output->getLines())
                ->filter(fn (string $line) => Str::contains($line, '[ERROR]'))
                ->map(fn (string $line) => Str::of($line)->after('[ERROR]')->replace($task->path, '')->trim())
                ->implode(PHP_EOL);

            $fail(__('The MySql config file is invalid.  :error', [
                'error' => $error,
            ]));
        }
    }
}
