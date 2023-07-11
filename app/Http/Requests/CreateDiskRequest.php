<?php

namespace App\Http\Requests;

use App\Enum;
use App\FilesystemDriver;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class CreateDiskRequest extends FormRequest
{
    use DiskConfigurationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        /** @var FilesystemDriver */
        $filesystemDriver = $this->enum('filesystem_driver', FilesystemDriver::class);

        return [
            'name' => ['required', 'string'],
            'filesystem_driver' => ['required', Enum::rule(FilesystemDriver::class)],
            'configuration' => ['required', 'array'],
        ] + $this->rulesForFilesystemDriver($filesystemDriver);
    }
}
