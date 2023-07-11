<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Models\User;
use App\Tasks\InstallEddyBackupCli;
use App\View\Components\Backup as BackupView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class InstallBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 150;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Backup $backup, public ?User $user = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->backup->server->runTask(new InstallEddyBackupCli($this->backup->server))
            ->asRoot()
            ->dispatch();

        $contents = BackupView::build($this->backup);

        $this->backup->server->uploadAsRoot($this->backup->cronPath(), $contents);

        $this->backup->forceFill([
            'installed_at' => now(),
            'installation_failed_at' => null,
        ])->save();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->backup->forceFill(['installation_failed_at' => now()])->save();

        $this->backup->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__("Installation of backup ':backup'", ['backup' => "`{$this->backup->name}`"]))
            ->send();
    }
}
