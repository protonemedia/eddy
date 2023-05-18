<?php

namespace App\Http\Controllers;

use App\Jobs\MakeSoftwareDefaultOnServer;
use App\Jobs\RestartSoftwareOnServer;
use App\Models\Server;
use App\Server\Software;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class SoftwareController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Server $server)
    {
        return view('software.index', [
            'server' => $server,
            'software' => SpladeTable::for($server->installedSoftware()->map(function (Software $software) {
                return [
                    'id' => $software->value,
                    'name' => $software->getDisplayName(),
                    'hasRestartTask' => $software->restartTaskClass() ? true : false,
                    'hasUpdateAlternativesTask' => $software->updateAlternativesTask() ? true : false,
                ];
            })->sortBy(fn (array $software) => $software['name']))
                ->column('name', __('Name'))
                ->column('actions', __('Actions'), alignment: 'right'),
        ]);
    }

    /**
     * Make the specified resource the 'default' one with update-alternatives.
     */
    public function default(Server $server, Software $software)
    {
        dispatch(new MakeSoftwareDefaultOnServer($server, $software));

        $this->logActivity(__("Made ':software' the CLI default on server ':server'", ['software' => $software->getDisplayName(), 'server' => $server->name]), $server);

        Toast::success(__(':software will now be the CLI default on the server.', ['software' => $software->getDisplayName()]));

        return to_route('servers.software.index', $server);
    }

    /**
     * Restart the specified resource.
     */
    public function restart(Server $server, Software $software)
    {
        dispatch(new RestartSoftwareOnServer($server, $software));

        $this->logActivity(__("Restarted ':software' on server ':server'", ['software' => $software->getDisplayName(), 'server' => $server->name]), $server);

        Toast::success(__(':software will be restarted on the server.', ['software' => $software->getDisplayName()]));

        return to_route('servers.software.index', $server);
    }
}
