<?php

namespace App\Models;

use Exception;
use ProtoneMedia\Splade\Facades\Toast;

class CouldNotConnectToServerException extends Exception
{
    public function __construct(
        private Server $server,
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
        Toast::warning(__("Could not connect to the server ':server'", [
            'server' => $this->server->name,
        ]));

        return back(fallback: route('servers.show', $this->server));
    }
}
