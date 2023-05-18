<?php

namespace App\Tasks;

use Illuminate\Support\Collection;

class DeploySiteWithoutDowntime extends DeploySite implements HasCallbacks
{
    public function sharedDirectories(): array
    {
        return Collection::make($this->site->shared_directories)
            ->map(fn ($directory) => trim($directory, '/'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function writeableDirectories(): array
    {
        return Collection::make($this->site->writeable_directories)
            ->map(fn ($directory) => trim($directory, '/'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function sharedFiles(): array
    {
        return Collection::make($this->site->shared_files)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'currentDirectory' => "{$this->site->path}/current",
            'sharedDirectory' => "{$this->site->path}/shared",
            'releasesDirectory' => "{$this->site->path}/releases",
            'releaseDirectory' => "{$this->site->path}/releases/{$this->deployment->created_at->timestamp}",
        ]);
    }
}
