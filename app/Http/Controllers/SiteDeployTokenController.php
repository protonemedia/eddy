<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Support\Str;
use ProtoneMedia\Splade\Facades\Toast;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class SiteDeployTokenController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function __invoke(Server $server, Site $site)
    {
        $site->deploy_token = Str::random(32);
        $site->save();

        $this->logActivity(__("Updated deploy token of site ':address' on server ':server'", ['address' => $site->address, 'server' => $server->name]), $site);

        Toast::success(__('The deploy token has been regenerated.'));

        return to_route('servers.sites.show', [$server, $site]);
    }
}
