<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 *
 * @mixin \App\Models\Cron
 */
class CronResource extends JsonResource
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
            'command' => $this->command,
            'expression' => $this->expression,
            'frequency' => $this->frequency,
            'custom_expression' => $this->frequency === 'custom' ? $this->custom_expression : null,
            'user' => $this->user,
        ];
    }
}
