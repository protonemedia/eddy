<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 *
 * @mixin \App\Models\FirewallRule
 */
class FirewallRuleResource extends JsonResource
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
            'action' => $this->action,
            'port' => $this->port,
            'from_ipv4' => $this->from_ipv4,
        ];
    }
}
