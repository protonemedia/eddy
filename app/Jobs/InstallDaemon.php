<?php

namespace App\Jobs;

use App\Models\Daemon;
use App\Models\User;
use App\Tasks\ReloadSupervisor;
use App\View\Components\SupervisorProgram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class InstallDaemon implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Daemon $daemon, public ?User $user = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $contents = SupervisorProgram::build($this->daemon);

        $this->daemon->server->uploadAsRoot($this->daemon->path(), $contents);

        $this->daemon->server->runTask(ReloadSupervisor::class)->asRoot()->dispatch();

        $this->daemon->forceFill([
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
        $this->daemon->forceFill(['installation_failed_at' => now()])->save();

        $this->daemon->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__("Installation of daemon ':daemon'", ['daemon' => "`{$this->daemon->command}`"]))
            ->send();
    }
}
