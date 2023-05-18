<?php

namespace App\Models;

trait InstallsAsynchronously
{
    /**
     * Marks the uninstallation request.
     */
    public function markUninstallationRequest(): self
    {
        $this->forceFill([
            'installation_failed_at' => null,
            'uninstallation_failed_at' => null,
            'uninstallation_requested_at' => now(),
        ])->save();

        return $this;
    }
}
