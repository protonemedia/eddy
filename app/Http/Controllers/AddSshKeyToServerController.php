<?php

namespace App\Http\Controllers;

use App\Jobs\AddSshKeyToServer;
use App\Models\Server;
use App\Models\SshKey;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use ProtoneMedia\Splade\Facades\Toast;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class AddSshKeyToServerController extends Controller
{
    /**
     * Show a form to add an SSH key to a server.
     */
    public function create(SshKey $sshKey)
    {
        return view('ssh-keys.add-to-servers', [
            'sshKey' => $sshKey,
            'servers' => $this->team()->servers()->get()->mapWithKeys(function (Server $server) {
                return [$server->id => $server->name_with_ip];
            }),
        ]);
    }

    /**
     * Add an SSH key to a server.
     */
    public function store(Request $request, SshKey $sshKey)
    {
        $request->validate([
            'servers' => ['required', 'array', 'min:1'],
            'servers.*' => ['required', Rule::exists('servers', 'id')->where(function ($query) {
                $query->where('team_id', $this->team()->id);
            })],
        ]);

        $request->collect('servers')->each(function ($serverId) use ($sshKey) {
            $server = $this->team()->servers()->findOrFail($serverId);

            dispatch(new AddSshKeyToServer($sshKey, $server));
        });

        Toast::message(__('The SSH Key will be added to the selected servers. This may take a few minutes.'));

        return to_route('ssh-keys.index');
    }
}
