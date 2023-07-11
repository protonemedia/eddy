<?php

namespace App\Http\Requests;

use App\Models\Disk;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class UpdateDiskRequest extends FormRequest
{
    use DiskConfigurationRules {
        prepareForValidation as prepareForValidationTrait;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Merge the configuration secrets that are not
     * submitted back into the request.
     */
    public function prepareForValidation(): void
    {
        /** @var Disk */
        $disk = $this->route('disk');

        $configuration = $disk->configuration->toArray();

        // The secrets are optional when updating a disk.
        Collection::make(Disk::secretConfigurationKeys())
            ->filter(function (string $secret) use ($configuration) {
                // Only use the secret if it is not submitted and it is already stored in the database.
                return $this->input("configuration.{$secret}") === null && array_key_exists($secret, $configuration);
            })
            ->each(function (string $secret) use ($configuration) {
                // It would be much easier to use mergeIfMissing() here, but unfortunately
                // that method doesn't support dotted keys.
                $newConfiguration = array_merge($this->input('configuration'), [
                    $secret => $configuration[$secret],
                ]);

                $this->merge(['configuration' => $newConfiguration]);
            });

        $this->prepareForValidationTrait();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        /** @var Disk */
        $disk = $this->route('disk');

        $filesystemDriver = $disk->filesystem_driver;

        return [
            'name' => ['required', 'string'],
            'configuration' => ['required', 'array'],
        ] + $this->rulesForFilesystemDriver($filesystemDriver);
    }
}
