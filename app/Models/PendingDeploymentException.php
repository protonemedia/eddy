<?php

namespace App\Models;

use Exception;
use ProtoneMedia\Splade\Facades\Toast;

class PendingDeploymentException extends Exception
{
    public function __construct(
        private Site $site,
        string $message = '',
        int $code = 0,
        Exception $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception.
     */
    public function render()
    {
        Toast::warning(__('The site is already being deployed.'));

        return back(fallback: route('servers.sites.show', [$this->site->server, $this->site]));
    }
}
