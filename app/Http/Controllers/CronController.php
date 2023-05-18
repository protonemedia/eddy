<?php

namespace App\Http\Controllers;

use App\Jobs\InstallCron;
use App\Jobs\UninstallCron;
use App\Models\Cron;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class CronController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Cron::class, 'cron');
    }

    /**
     * An array of default frequencies.
     */
    private function frequencyOptions(): array
    {
        return [
            '* * * * *' => __('Every minute'),
            '*/5 * * * *' => __('Every 5 minutes'),
            '0 * * * *' => __('Hourly'),
            '0 0 * * *' => __('Daily'),
            '0 0 * * 0' => __('Weekly'),
            '0 0 1 * *' => __('Monthly'),
            '@reboot' => __('On Reboot'),
            'custom' => __('Custom expression'),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Server $server)
    {
        $frequencies = $this->frequencyOptions();

        return view('crons.index', [
            'server' => $server,
            'crons' => SpladeTable::for($server->crons())
                ->column('command', __('Command'))
                ->column('user', __('User'))
                ->column('expression', __('Frequency'), as: fn ($expression) => $frequencies[$expression] ?? $expression)
                ->column('status', __('Status'), alignment: 'right')
                ->rowModal(fn (Cron $cron) => route('servers.crons.edit', [$server, $cron]))
                ->defaultSort('command')
                ->paginate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Server $server)
    {
        return view('crons.create', [
            'server' => $server,
            'frequencies' => $this->frequencyOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Server $server, Request $request)
    {
        $request->validate([
            'command' => ['required', 'string', 'max:255'],
            'user' => ['required', 'string', 'max:255', 'in:root,'.$server->username],
            'frequency' => ['required', 'string', 'max:255', Rule::in(array_keys($this->frequencyOptions()))],
            'custom_expression' => ['required_if:frequency,custom', 'nullable', 'string', 'max:255'],
        ]);

        $data = $request->only('command', 'user') + [
            'expression' => $request->input('frequency') === 'custom'
                ? $request->input('custom_expression')
                : $request->input('frequency'),
        ];

        /** @var Cron */
        $cron = $server->crons()->create($data);

        $this->logActivity(__("Created cron ':command' on server ':server'", ['command' => $cron->command, 'server' => $server->name]), $cron);

        dispatch(new InstallCron($cron, $this->user()));

        Toast::message(__('The Cron has been created and will be installed on the server.'));

        return to_route('servers.crons.index', $server);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server, Cron $cron)
    {
        $frequencyOptions = $this->frequencyOptions();

        $cron->frequency = array_key_exists($cron->expression, Arr::except($frequencyOptions, 'custom')) ? $cron->expression : 'custom';

        if ($cron->frequency === 'custom') {
            $cron->custom_expression = $cron->expression;
        }

        return view('crons.edit', [
            'cron' => $cron,
            'server' => $server,
            'frequencies' => $this->frequencyOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server, Cron $cron)
    {
        $request->validate([
            'command' => ['required', 'string', 'max:255'],
            'user' => ['required', 'string', 'max:255', 'in:root,'.$server->username],
            'frequency' => ['required', 'string', 'max:255', Rule::in(array_keys($this->frequencyOptions()))],
            'custom_expression' => ['required_if:frequency,custom', 'nullable', 'string', 'max:255'],
        ]);

        $data = $request->only('command', 'user') + [
            'installed_at' => null,
            'installation_failed_at' => null,
            'uninstallation_failed_at' => null,
            'expression' => $request->input('frequency') === 'custom'
                ? $request->input('custom_expression')
                : $request->input('frequency'),
        ];

        $cron->forceFill($data)->save();

        $this->logActivity(__("Updated cron ':command' on server ':server'", ['command' => $cron->command, 'server' => $server->name]), $cron);

        dispatch(new InstallCron($cron, $this->user()));

        Toast::message(__('The Cron will be updated on the server.'));

        return to_route('servers.crons.index', $server);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Server $server, Cron $cron)
    {
        $cron->markUninstallationRequest();

        dispatch(new UninstallCron($cron, $this->user()));

        $this->logActivity(__("Deleted cron ':command' from server ':server'", ['command' => $cron->command, 'server' => $server->name]), $cron);

        Toast::message(__('The Cron will be uninstalled from the server.'));

        return to_route('servers.crons.index', $server);
    }
}
