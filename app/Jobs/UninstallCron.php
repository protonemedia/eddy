<?php

namespace App\Jobs;

use App\Models\Cron;
use App\Models\User;
use App\Tasks\DeleteFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UninstallCron implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Cron $cron, public ?User $user = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cron->server->runTask(new DeleteFile($this->cron->path()))->asRoot()->dispatch();

        $this->cron->delete();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->cron->forceFill(['uninstallation_failed_at' => now()])->save();

        $this->cron->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__("Uninstallation of cron ':cron'", ['cron' => "`{$this->cron->command}`"]))
            ->send();
    }
}
