<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Models\User;
use App\Tasks\DeleteFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UninstallBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->backup->server->runTask(new DeleteFile($this->backup->cronPath()))->asRoot()->dispatch();

        $this->backup->delete();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->backup->forceFill(['uninstallation_failed_at' => now()])->save();

        $this->backup->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__("Uninstallation of backup ':backup'", ['backup' => "`{$this->backup->name}`"]))
            ->send();
    }
}
