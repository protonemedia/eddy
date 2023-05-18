<?php

namespace App\Http\Controllers;

use App\Jobs\RemoveSshKeyFromServer;
use App\Models\Server;
use App\Models\SshKey;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use ProtoneMedia\Splade\Facades\Toast;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class RemoveSshKeyFromServerController extends Controller
{
    /**
     * Show a form to remove an SSH key to a server.
     */
    public function edit(SshKey $sshKey)
    {
        return view('ssh-keys.remove-from-servers', [
            'sshKey' => $sshKey,
            'servers' => $this->team()->servers()->get()->mapWithKeys(function (Server $server) {
                return [$server->id => $server->name_with_ip];
            }),
        ]);
    }

    /**
     * Remove an SSH key from a server.
     */
    public function destroy(Request $request, SshKey $sshKey)
    {
        $request->validate([
            'servers' => ['required', 'array', 'min:1'],
            'servers.*' => ['required', Rule::exists('servers', 'id')->where(function ($query) {
                $query->where('team_id', $this->team()->id);
            })],
        ]);

        $request->collect('servers')->each(function ($serverId) use ($sshKey) {
            $server = $this->team()->servers()->findOrFail($serverId);

            dispatch(new RemoveSshKeyFromServer($sshKey->public_key, $server));
        });

        Toast::message(__('The SSH Key will be removed from the selected servers. This may take a few minutes.'));

        return to_route('ssh-keys.index');
    }
}
