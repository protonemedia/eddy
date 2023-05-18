<?php

namespace App\Http\Requests;

use App\Models\Credentials;
use App\Provider;
use App\Rules\DigitalOceanToken;
use App\Rules\HetznerCloudToken;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class UpdateCredentialsRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        /** @var Credentials */
        $credentials = $this->route('credentials');

        $providerAttribute = match ($credentials->provider) {
            Provider::DigitalOcean => 'digital_ocean_token',
            Provider::HetznerCloud => 'hetzner_cloud_token',
            default => null,
        };

        if (! $providerAttribute) {
            // This provider has no credentials.
            return $rules;
        }

        $providerKey = "credentials.{$providerAttribute}";

        if (! $this->input($providerKey)) {
            // No credentials were provided.
            return $rules;
        }

        $providerRules = match ($credentials->provider) {
            Provider::DigitalOcean => ['required', 'string', new DigitalOceanToken],
            Provider::HetznerCloud => ['required', 'string', new HetznerCloudToken],
            default => ['prohibited'],
        };

        return $rules + [
            'credentials' => ['required', 'array'],
            $providerKey => $providerRules,
        ];
    }
}
