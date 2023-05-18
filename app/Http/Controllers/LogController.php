<?php

namespace App\Http\Controllers;

use App\FileOnServer;
use App\Models\Server;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Server $server)
    {
        return view('logs.index', [
            'server' => $server,
            'logs' => SpladeTable::for($server->files()->logFiles())
                ->column('name', __('Name'))
                ->column('description', __('Description'))
                ->rowModal(fn (FileOnServer $file) => $file->showRoute($server)),
        ]);
    }
}
