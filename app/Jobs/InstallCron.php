<?php

namespace App\Jobs;

use App\Models\Cron as CronModel;
use App\Models\User;
use App\View\Components\Cron as CronView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class InstallCron implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public CronModel $cron, public ?User $user = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $contents = CronView::build($this->cron);

        $this->cron->server->uploadAsRoot($this->cron->path(), $contents);

        $this->cron->forceFill([
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
        $this->cron->forceFill(['installation_failed_at' => now()])->save();

        $this->cron->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__("Installation of cron ':cron'", ['cron' => "`{$this->cron->command}`"]))
            ->send();
    }
}
