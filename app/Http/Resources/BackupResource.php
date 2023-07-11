<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 *
 * @mixin \App\Models\Backup
 */
class BackupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'disk_id' => $this->disk_id,
            'include_files' => implode(PHP_EOL, $this->include_files->toArray()),
            'exclude_files' => implode(PHP_EOL, $this->exclude_files->toArray()),
            'retention' => $this->retention,
            'cron_expression' => $this->cron_expression,
            'frequency' => $this->frequency,
            'custom_expression' => $this->frequency === 'custom' ? $this->custom_expression : null,
            'notification_on_failure' => $this->notification_on_failure,
            'notification_on_success' => $this->notification_on_success,
            'notification_email' => $this->notification_email,
            'databases' => $this->databases->modelKeys(),
        ];
    }
}
