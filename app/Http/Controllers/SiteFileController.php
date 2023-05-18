<?php

namespace App\Http\Controllers;

use App\FileOnServer;
use App\Models\Server;
use App\Models\Site;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class SiteFileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Server $server, Site $site)
    {
        return view('sites.files.index', [
            'server' => $server,
            'site' => $site,
            'files' => SpladeTable::for($site->files()->editableFiles())
                ->column('name', __('Name'))
                ->column('description', __('Description'))
                ->rowModal(fn (FileOnServer $file) => $file->editRoute($server)),
        ]);
    }
}
