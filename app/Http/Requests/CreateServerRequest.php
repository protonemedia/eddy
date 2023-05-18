<?php

namespace App\Http\Requests;

use App\Infrastructure\Entities\Image;
use App\Infrastructure\Entities\Region;
use App\Infrastructure\Entities\ServerType;
use App\Infrastructure\ProviderFactory;
use App\Infrastructure\ServerProvider;
use App\Models\Credentials;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class CreateServerRequest extends FormRequest
{
    public function __construct(private ProviderFactory $providerFactory)
    {
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->currentTeam->subscriptionOptions()->canCreateServer();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $credentialsExistsRule = Rule::exists('credentials', 'id')->where(function (Builder $query) {
            $query->where('user_id', $this->user()->id)
                ->where(fn (Builder $query) => $query->whereNull('team_id')->orWhere('team_id', $this->user()->currentTeam->id));
        });

        return [
            'name' => ['required', 'string', 'max:255'],
            'credentials_id' => ['nullable', 'required_unless:custom_server,1', $credentialsExistsRule],
            'custom_server' => ['boolean'],
            'public_ipv4' => ['nullable', 'required_if:custom_server,1', 'ipv4'],
            'region' => ['nullable', 'required_unless:custom_server,1'],
            'type' => ['nullable', 'required_unless:custom_server,1'],
            'image' => ['nullable', 'required_unless:custom_server,1'],
            'ssh_keys' => ['array'],
            'ssh_keys.*' => [Rule::exists('ssh_keys', 'id')->where('user_id', $this->user()->id)],
            'add_key_to_github' => ['boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        if ($this->boolean('custom_server')) {
            return;
        }

        $validator->after(function (Validator $validator) {

            $credentials = Credentials::query()->whereUserId($this->user()->id)->find($this->input('credentials_id'));

            if (! $credentials) {
                // The credentials_id field will already have a validation error
                return;
            }

            /** @var ServerProvider */
            $provider = $this->providerFactory->forCredentials($credentials);

            /** @var Region|null */
            $region = $provider->findAvailableServerRegions()->first(function (Region $region) {
                return $region->id === $this->input('region');
            }, function () use ($validator) {
                $validator->errors()->add('region', 'The selected region is invalid.');
            });

            if (! $region) {
                // We need a valid region to continue
                return;
            }

            $provider->findAvailableServerTypesByRegion($region->id)->first(function (ServerType $type) {
                return $type->id === $this->input('type');
            }, function () use ($validator) {
                $validator->errors()->add('type', 'The selected type is invalid.');
            });

            $provider->findAvailableServerImagesByRegion($region->id)->first(function (Image $image) {
                return $image->id === $this->input('image');
            }, function () use ($validator) {
                $validator->errors()->add('image', 'The selected image is invalid.');
            });
        });
    }
}
