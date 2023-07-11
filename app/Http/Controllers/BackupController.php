<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBackupRequest;
use App\Http\Requests\UpdateBackupRequest;
use App\Jobs\InstallBackup;
use App\Jobs\UninstallBackup;
use App\Models\Backup;
use App\Models\BackupJob;
use App\Models\BackupJobStatus;
use App\Models\Cron;
use App\Models\Server;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class BackupController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Backup::class, 'backup');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Server $server)
    {
        $frequencies = Cron::predefinedFrequencyOptions();

        return view('backups.index', [
            'server' => $server,
            'backups' => SpladeTable::for($server->backups()->with([
                'databases', 'latestJob',
            ]))
                ->column('name', __('Name'))
                ->column('disk.name', __('Disk'))
                ->column('cron_expression', __('Frequency'), as: fn ($expression) => $frequencies[$expression] ?? $expression)
                ->column('databases', __('Databases'), as: fn ($databases) => $databases->pluck('name')->join(PHP_EOL))
                ->column('status', __('Status'), alignment: 'right')
                ->rowLink(fn (Backup $backup) => route('servers.backups.show', [$server, $backup]))
                ->defaultSort('name')
                ->paginate(),

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Server $server)
    {
        if (! $this->team()->subscriptionOptions()->hasBackups()) {
            Toast::center()->autoDismiss(0)->backdrop()->warning(
                __('You need to upgrade your subscription to create backups.')
            );

            return back(fallback: route('servers.backups.index', $server));
        }

        return view('backups.create', [
            'server' => $server,
            'frequencies' => Cron::predefinedFrequencyOptions(),
            'disks' => $this->user()->disks->pluck('name', 'id'),
            'databases' => $server->databases->pluck('name', 'id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBackupRequest $request, Server $server)
    {
        /** @var Backup */
        $backup = $server->backups()->make($request->safe([
            'name',
            'disk_id',
            'include_files',
            'exclude_files',
            'cron_expression',
            'retention',
            'notification_email',
            'notification_on_failure',
            'notification_on_success',
        ]))->forceFill([
            'created_by_user_id' => $this->user()->id,
        ]);

        $backup->save();
        $backup->databases()->attach($request->validated('databases'));

        $this->logActivity(__("Updated backup ':name'", ['name' => $backup->name]));

        dispatch(new InstallBackup($backup->fresh(), $this->user()->fresh()));

        Toast::message(__('The backup will be installed shortly.'));

        return to_route('servers.backups.index', $server);
    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server, Backup $backup)
    {
        $frequencies = Cron::predefinedFrequencyOptions();

        $jobs = SpladeTable::for($backup->jobs())
            ->column('created_at', __('Date'), as: fn (Carbon $date) => $date->diffForHumans())
            ->column('size_in_mb', __('Size'), as: fn (int $size) => "{$size} MB")
            ->column('status', __('Status'), as: fn (BackupJobStatus $status) => $status->name, alignment: 'right')
            ->rowModal(fn (BackupJob $backupJob) => route('servers.backup-jobs.show', [$server, $backupJob]))
            ->defaultSortDesc('created_at')
            ->paginate();

        return view('backups.show', [
            'frequencies' => $frequencies,
            'jobs' => $jobs,
            'backup' => $backup,
            'server' => $server,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server, Backup $backup)
    {
        $frequencyOptions = Cron::predefinedFrequencyOptions();

        $backup->frequency = array_key_exists($backup->cron_expression, Arr::except($frequencyOptions, 'custom')) ? $backup->cron_expression : 'custom';

        if ($backup->frequency === 'custom') {
            $backup->custom_expression = $backup->cron_expression;
        }

        return view('backups.edit', [
            'backup' => $backup,
            'server' => $server,
            'frequencies' => $frequencyOptions,
            'disks' => $this->user()->disks->pluck('name', 'id'),
            'databases' => $server->databases->pluck('name', 'id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBackupRequest $request, Server $server, Backup $backup)
    {
        $backup->fill($request->safe([
            'name',
            'disk_id',
            'include_files',
            'exclude_files',
            'cron_expression',
            'retention',
            'notification_email',
            'notification_on_failure',
            'notification_on_success',
        ]))->forceFill([
            'installed_at' => null,
            'installation_failed_at' => null,
            'uninstallation_failed_at' => null,
        ])->save();

        $backup->databases()->sync($request->validated('databases'));

        $this->logActivity(__("Updated backup ':name'", ['name' => $backup->name]));

        dispatch(new InstallBackup($backup->fresh(), $this->user()->fresh()));

        Toast::message(__('The backup will be deployed shortly.'));

        return to_route('servers.backups.index', $server);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Server $server, Backup $backup)
    {
        $backup->markUninstallationRequest();

        dispatch(new UninstallBackup($backup, $this->user()));

        $this->logActivity(__("Deleted backup ':backup' from server ':server'", ['backup' => $backup->name, 'server' => $server->name]), $backup);

        Toast::message(__('The Backup will be uninstalled from the server.'));

        return to_route('servers.backups.index', $server);
    }
}
