<?php

namespace App\Http\Resources;

use App\Models\Disk;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 *
 * @mixin \App\Models\Disk
 */
class DiskResource extends JsonResource
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
            'filesystem_driver' => $this->filesystem_driver,
            'configuration' => Arr::except($this->configuration->toArray(), Disk::secretConfigurationKeys()),
        ];
    }
}
