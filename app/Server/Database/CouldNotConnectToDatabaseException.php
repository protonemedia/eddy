<?php

namespace App\Server\Database;

use App\Models\Server;
use Exception;
use ProtoneMedia\Splade\Facades\Toast;

class CouldNotConnectToDatabaseException extends Exception
{
    public function __construct(
        public Server $server,
        public $message
    ) {
        parent::__construct($message);
    }

    public function render()
    {
        Toast::warning(__("Could not connect to the database on server ':server'", [
            'server' => $this->server->name,
        ]));

        return back(fallback: route('servers.show', $this->server));
    }
}
