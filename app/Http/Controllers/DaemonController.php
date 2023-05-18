<?php

namespace App\Http\Controllers;

use App\Enum;
use App\Jobs\InstallDaemon;
use App\Jobs\UninstallDaemon;
use App\Models\Daemon;
use App\Models\Server;
use App\Signal;
use Illuminate\Http\Request;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class DaemonController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Daemon::class, 'daemon');
    }

    /**
     * An array of default signals.
     */
    private function signalOptions(): array
    {
        return Enum::options(Signal::class, true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Server $server)
    {
        return view('daemons.index', [
            'server' => $server,
            'daemons' => SpladeTable::for($server->daemons())
                ->column('command', __('Command'))
                ->column('user', __('User'))
                ->column('processes', __('Processes'))
                ->column('status', __('Status'), alignment: 'right')
                ->rowModal(fn (Daemon $daemon) => route('servers.daemons.edit', [$server, $daemon]))
                ->defaultSort('command')
                ->paginate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Server $server)
    {
        return view('daemons.create', [
            'server' => $server,
            'signals' => $this->signalOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Server $server, Request $request)
    {
        $data = $request->validate([
            'command' => ['required', 'string', 'max:255'],
            'directory' => ['nullable', 'string', 'max:255'],
            'user' => ['required', 'string', 'max:255', 'in:root,'.$server->username],
            'processes' => ['required', 'integer', 'min:1'],
            'stop_wait_seconds' => ['required', 'integer', 'min:0'],
            'stop_signal' => ['required', 'string', 'max:255', Enum::rule(Signal::class)],
        ]);

        /** @var Daemon */
        $daemon = $server->daemons()->create($data);

        $this->logActivity(__("Created daemon ':command' on server ':server'", ['command' => $daemon->command, 'server' => $server->name]), $daemon);

        dispatch(new InstallDaemon($daemon, $this->user()));

        Toast::message(__('The Daemon has been created and will be installed on the server.'));

        return to_route('servers.daemons.index', $server);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server, Daemon $daemon)
    {
        return view('daemons.edit', [
            'daemon' => $daemon,
            'server' => $server,
            'signals' => $this->signalOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server, Daemon $daemon)
    {
        $data = $request->validate([
            'command' => ['required', 'string', 'max:255'],
            'directory' => ['nullable', 'string', 'max:255'],
            'user' => ['required', 'string', 'max:255', 'in:root,'.$server->username],
            'processes' => ['required', 'integer', 'min:1'],
            'stop_wait_seconds' => ['required', 'integer', 'min:0'],
            'stop_signal' => ['required', 'string', 'max:255', Enum::rule(Signal::class)],
        ]);

        $daemon->forceFill(['installed_at' => null])->update($data);

        $this->logActivity(__("Updated daemon ':command' on server ':server'", ['command' => $daemon->command, 'server' => $server->name]), $daemon);

        dispatch(new InstallDaemon($daemon, $this->user()));

        Toast::message(__('The Daemon will be updated on the server.'));

        return to_route('servers.daemons.index', $server);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Server $server, Daemon $daemon)
    {
        $daemon->markUninstallationRequest();

        dispatch(new UninstallDaemon($daemon, $this->user()));

        $this->logActivity(__("Deleted daemon ':command' from server ':server'", ['command' => $daemon->command, 'server' => $server->name]), $daemon);

        Toast::message(__('The Daemon will be uninstalled from the server.'));

        return to_route('servers.daemons.index', $server);
    }
}
