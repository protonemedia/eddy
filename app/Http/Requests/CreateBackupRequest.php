<?php

namespace App\Http\Requests;

use App\Models\Cron;
use App\Models\Server;
use App\Rules\CronExpression;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class CreateBackupRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'include_files' => Arr::explodePaths($this->input('include_files')),
            'exclude_files' => Arr::explodePaths($this->input('exclude_files')),
            'cron_expression' => $this->input('frequency') === 'custom'
                ? $this->input('custom_expression')
                : $this->input('frequency'),
        ]);

        if (! $this->boolean('notification_on_failure') && ! $this->boolean('notification_on_success')) {
            // Clear the email if no notifications are enabled
            $this->merge([
                'notification_email' => null,
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->currentTeam->subscriptionOptions()->hasBackups();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        /** @var Server */
        $server = $this->route('server');

        return [
            'name' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'max:255', Rule::in(array_keys(Cron::predefinedFrequencyOptions()))],
            'custom_expression' => ['required_if:frequency,custom', 'nullable', 'string', 'max:255', new CronExpression],
            'cron_expression' => [],
            'retention' => ['nullable', 'integer', 'min:2'],
            'disk_id' => ['required', Rule::exists('disks', 'id')->where('user_id', $this->user()->id)],
            'databases' => ['nullable', 'required_without:include_files', 'array'],
            'databases.*' => [Rule::exists('databases', 'id')->where('server_id', $server->id)],
            'include_files' => ['nullable', 'required_without:databases', 'array'],
            'include_files.*' => ['string'],
            'exclude_files' => ['nullable', 'array'],
            'exclude_files.*' => ['string'],
            'notification_on_failure' => ['nullable', 'boolean'],
            'notification_on_success' => ['nullable', 'boolean'],
            'notification_email' => ['nullable', 'required_if:notification_on_failure,1', 'required_if:notification_on_success,1', 'string', 'email', 'max:255'],
        ];
    }
}
