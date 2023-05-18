<?php

namespace App\Http\Controllers;

use App\Jobs\InstallDatabase;
use App\Jobs\InstallDatabaseUser;
use App\Jobs\UninstallDatabase;
use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\Rule;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class DatabaseController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Database::class, 'database');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Server $server)
    {
        $this->authorize('viewAny', DatabaseUser::class);

        return view('databases.index', [
            'server' => $server,

            'databases' => SpladeTable::for($server->databases())
                ->column('name', __('Database'))
                ->column('status', __('Status'), alignment: 'right')
                ->rowModal(fn (Database $database) => route('servers.databases.edit', [$server, $database])),

            'users' => SpladeTable::for($server->databaseUsers())
                ->column('name', __('User'))
                ->column('status', __('Status'), alignment: 'right')
                ->rowModal(fn (DatabaseUser $databaseUser) => route('servers.database-users.edit', [$server, $databaseUser])),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Server $server)
    {
        return view('databases.create', [
            'server' => $server,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Server $server, Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('databases', 'name')->where('server_id', $server->id)],
            'create_user' => ['required', 'boolean'],
            'user' => ['nullable', 'required_if:create_user,1', 'string', 'max:255', Rule::unique('database_users', 'name')->where('server_id', $server->id)],
            'password' => ['nullable', 'required_if:create_user,1', 'string', 'max:255'],
        ]);

        /** @var Database */
        $database = $server->databases()->create([
            'name' => $data['name'],
        ]);

        $this->logActivity(__("Created database ':name' on server ':server'", ['name' => $database->name, 'server' => $server->name]), $database);

        if (! $data['create_user']) {
            dispatch(new InstallDatabase($database, $this->user()));

            Toast::message(__('The database will be created shortly.'));

            return to_route('servers.databases.index', $server);
        }

        /** @var DatabaseUser */
        $databaseUser = $database->users()->make([
            'name' => $data['user'],
        ])->forceFill([
            'server_id' => $server->id,
        ]);

        $databaseUser->save();
        $databaseUser->databases()->attach($database);

        $this->logActivity(__("Created database user ':name' on server ':server'", ['name' => $databaseUser->name, 'server' => $server->name]), $databaseUser);

        Bus::chain([
            new InstallDatabase($database, $this->user()->fresh()),
            new InstallDatabaseUser($databaseUser, $data['password'], $this->user()->fresh()),
        ])->dispatch();

        Toast::message(__('The database and user will be created shortly.'));

        return to_route('servers.databases.index', $server);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server, Database $database)
    {
        return view('databases.edit', [
            'database' => $database,
            'server' => $server,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Server $server, Database $database)
    {
        $database->markUninstallationRequest();

        dispatch(new UninstallDatabase($database, $this->user()));

        $this->logActivity(__("Deleted database ':name' from server ':server'", ['name' => $database->name, 'server' => $server->name]), $database);

        Toast::message(__('The database will be uninstalled from the server.'));

        return to_route('servers.databases.index', $server);
    }
}
