<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateServerRequest;
use App\Infrastructure\Entities\ServerStatus;
use App\Jobs\DeleteServerFromInfrastructure;
use App\KeyPairGenerator;
use App\Models\Credentials;
use App\Models\Server;
use App\Models\SshKey;
use App\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class ServerController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Server::class, 'server');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('servers.index', [
            'servers' => SpladeTable::for($this->team()->servers())
                ->column('name', __('Name'))
                ->column('provider_name', __('Provider'))
                ->column('public_ipv4', __('IP Address'), classes: 'tabular-nums')
                ->column('status', __('Status'), alignment: 'right')
                ->rowLink(fn (Server $server) => route('servers.show', $server))
                ->defaultSort('name')
                ->paginate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (! $this->team()->subscriptionOptions()->canCreateServer()) {
            Toast::center()
                ->backdrop()
                ->autoDismiss(0)
                ->warning(__('You have reached the maximum number of servers for your current subscription plan.'));

            return to_route('servers.index');
        }

        $credentials = $this->user()
            ->credentials()
            ->provider(Provider::forServers())
            ->canBeUsedByTeam($this->team())
            ->select('id', 'name', 'provider')
            ->get()
            ->mapWithKeys(fn (Credentials $credentials) => [$credentials->id => $credentials->nameWithProvider]);

        if ($credentials->isEmpty() && ! $request->query('withoutCredentials')) {
            return view('servers.credentials-missing');
        }

        $sshKeys = $this->user()
            ->sshKeys()
            ->select('id', 'name')
            ->get()
            ->mapWithKeys(fn (SshKey $sshKey) => [$sshKey->id => $sshKey->name]);

        $defaultCredentials = $request->query('credentials') && $credentials->has($request->query('credentials'))
            ? $request->query('credentials')
            : null;

        return view('servers.create', [
            'defaultCredentials' => $defaultCredentials,
            'credentials' => $credentials,
            'sshKeys' => $sshKeys,
            'hasGithubCredentials' => $this->user()->hasGithubCredentials(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateServerRequest $request, KeyPairGenerator $keyPairGenerator)
    {
        $customServer = $request->boolean('custom_server');

        /** @var Credentials|null */
        $credentials = $customServer ? null : $this->user()->credentials()
            ->canBeUsedByTeam($this->team())
            ->findOrFail($request->input('credentials_id'));

        /** @var Server */
        $server = $this->team()->servers()->make([
            'name' => $request->input('name'),
            'credentials_id' => $credentials?->id,
            'region' => $request->input('region'),
            'type' => $request->input('type'),
            'image' => $request->input('image'),
        ]);

        $keyPair = $keyPairGenerator->ed25519();
        $server->public_key = $keyPair->publicKey;
        $server->private_key = $keyPair->privateKey;

        $server->working_directory = config('eddy.server_defaults.working_directory');
        $server->ssh_port = config('eddy.server_defaults.ssh_port');
        $server->username = config('eddy.server_defaults.username');

        $server->password = Str::password(symbols: false);
        $server->database_password = Str::password(symbols: false);

        $server->public_ipv4 = $customServer ? $request->input('public_ipv4') : null;
        $server->provider = $customServer ? Provider::CustomServer : $credentials->provider;
        $server->created_by_user_id = $this->user()->id;

        $server->save();
        $server->dispatchCreateAndProvisionJobs(
            SshKey::whereKey($request->input('ssh_keys'))->get(),
            $request->boolean('add_key_to_github') ? $this->user()->githubCredentials : null,
        );

        $this->logActivity(__("Created server ':server'", ['server' => $server->name]), $server);

        Toast::success(__('Your server is being created and provisioned.'));

        return to_route('servers.show', $server);
    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server)
    {
        if ($server->status === ServerStatus::Deleting) {
            Toast::warning(__('Your server is being deleted.'));

            return to_route('servers.index');
        }

        if (! $server->provisioned_at) {
            return view('servers.provisioning', [
                'server' => $server,
            ]);
        }

        return view('servers.show', [
            'server' => $server,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Server $server)
    {
        $server->forceFill([
            'status' => ServerStatus::Deleting,
            'uninstallation_requested_at' => now(),
        ])->save();

        dispatch(new DeleteServerFromInfrastructure($server, $this->user()));

        $this->logActivity(__("Deleted server ':server'", ['server' => $server->name]), $server);

        Toast::message(__('Your server is being deleted.'));

        return to_route('servers.index');
    }
}
