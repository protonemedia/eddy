<?php

namespace App\Http\Controllers;

use App\Jobs\RemoveSshKeyFromServer;
use App\Models\Server;
use App\Models\SshKey;
use App\Rules\PublicKey;
use Illuminate\Http\Request;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class SshKeyController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(SshKey::class, 'ssh_key');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('ssh-keys.index', [
            'teamHasServer' => $this->team()->servers()->exists(),
            'sshKeys' => SpladeTable::for($this->user()->sshKeys()->getQuery())
                ->column('name', __('Name'))
                ->column('fingerprint', __('MD5 Fingerprint'))
                ->column('actions', label: '', alignment: 'right')
                ->defaultSort('name')
                ->paginate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ssh-keys.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'public_key' => ['required', 'string', new PublicKey],
        ]);

        $this->user()->sshKeys()->create($data);

        Toast::message(__('SSH Key added.'));

        return to_route('ssh-keys.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, SshKey $sshKey)
    {
        if ($request->query('remove-from-servers')) {
            $this->team()->servers()->each(function (Server $server) use ($sshKey) {
                dispatch(new RemoveSshKeyFromServer($sshKey->public_key, $server));
            });

            Toast::message(__('The SSH Key will be deleted and removed from all servers. This may take a few minutes.'));
        } else {
            Toast::message(__('SSH Key deleted.'));
        }

        $sshKey->delete();

        return to_route('ssh-keys.index');
    }
}
