<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 *
 * @mixin \App\Models\Site
 */
class SiteResource extends JsonResource
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
            'address' => $this->address,
            'repository_url' => $this->repository_url,
            'repository_branch' => $this->repository_branch,
            'tls_setting' => $this->tls_setting,
            'php_version' => $this->php_version,
            'web_folder' => $this->web_folder,
            'deployment_releases_retention' => $this->deployment_releases_retention,
            'deploy_notification_email' => $this->deploy_notification_email,
            'shared_directories' => implode(PHP_EOL, $this->shared_directories->toArray()),
            'shared_files' => implode(PHP_EOL, $this->shared_files->toArray()),
            'writeable_directories' => implode(PHP_EOL, $this->writeable_directories->toArray()),
            'hook_before_updating_repository' => $this->hook_before_updating_repository,
            'hook_after_updating_repository' => $this->hook_after_updating_repository,
            'hook_before_making_current' => $this->hook_before_making_current,
            'hook_after_making_current' => $this->hook_after_making_current,
        ];
    }
}
