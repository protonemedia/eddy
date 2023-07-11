<?php

namespace App\Models;

use Exception;
use ProtoneMedia\Splade\Facades\Toast;

class CouldNotCreateBackupJobException extends Exception
{
    public function __construct(
        private Backup $backup,
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
        Toast::warning(__('The backup process of the previous job is still running.'));

        return back(fallback: route('servers.backups.show', [$this->backup->server, $this->backup]));
    }
}
