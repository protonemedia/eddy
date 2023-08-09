<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use phpseclib3\Crypt\PublicKeyLoader;
use Throwable;

class PublicKey implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            PublicKeyLoader::load($value)->__toString();
        } catch (Throwable $e) {
            if (! $this->validateWithSshKeygen($value)) {
                $fail(__('The :attribute is not valid.'));
            }
        }
    }

    /**
     * Validate the public key with ssh-keygen.
     */
    private function validateWithSshKeygen(string $publicKey): bool
    {
        if (strlen($publicKey) > 10000) {
            // Public keys longer than 10000 characters are very unlikely to be valid.
            return false;
        }

        $filesystem = new Filesystem;

        $tempFile = tempnam(sys_get_temp_dir(), md5($publicKey));
        register_shutdown_function(fn () => $filesystem->delete($tempFile));

        $filesystem->put($tempFile, $publicKey);

        $passes = Process::command(['ssh-keygen', '-l', '-f', $tempFile])->run()->successful();

        $filesystem->delete($tempFile);

        return $passes;
    }
}
