<?php

namespace App\Http\Controllers;

use App\Enum;
use App\Http\Requests\UpdateCredentialsRequest;
use App\Models\Credentials;
use App\Models\Team;
use App\Provider;
use App\Rules\DigitalOceanToken;
use App\Rules\HetznerCloudToken;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class CredentialsController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Credentials::class, 'credentials');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('credentials.index', [
            'credentials' => SpladeTable::for($this->user()->credentials()->with(['user', 'team']))
                ->column('name', __('Name'))
                ->column('provider_name', __('Provider'))
                ->column('team', __('Bound to team'), as: fn (Team $team = null) => $team ? $team->name : '-')
                ->column('actions', label: '', alignment: 'right')
                ->rowModal(fn (Credentials $credentials) => route('credentials.edit', $credentials))
                ->defaultSort('name')
                ->paginate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $forServer = $request->query('forServer') ? true : false;

        $options = Provider::userManagable();

        if (! $forServer && ! $this->user()->hasGithubCredentials()) {
            $options[] = Provider::Github;
        }

        return view('credentials.create', [
            'providers' => Enum::options($options),
            'forServer' => $forServer,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', Rule::in(Enum::values(Provider::userManagable()))],
            'credentials' => ['array'],
            'credentials.digital_ocean_token' => [Enum::requiredIf(Provider::DigitalOcean), 'nullable', 'string', new DigitalOceanToken],
            'credentials.hetzner_cloud_token' => [Enum::requiredIf(Provider::HetznerCloud), 'nullable', 'string', new HetznerCloudToken],
        ]);

        $credentials = $this->user()->credentials()->make($data);

        if ($request->boolean('bind_to_team')) {
            $credentials->team_id = $this->team()->id;
        }

        $credentials->save();

        Toast::message(__('Credentials added.'));

        $forServer = $request->query('forServer') ? true : false;

        return $forServer
            ? to_route('servers.create', ['credentials' => $credentials->id])
            : to_route('credentials.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Credentials $credentials)
    {
        return view('credentials.edit', [
            'credentials' => $credentials,
            'providers' => Enum::options(Provider::class),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCredentialsRequest $request, Credentials $credentials)
    {
        $data = $request->validated();

        $credentials->update($data);

        Toast::message(__('Credentials updated.'));

        return to_route('credentials.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Credentials $credentials)
    {
        $credentials->delete();

        Toast::message(__('Credentials deleted.'));

        return to_route('credentials.index');
    }
}
