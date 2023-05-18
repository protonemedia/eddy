<?php

namespace App\Jobs;

use App\Models\Site;
use App\Models\User;
use App\Tasks\UpdateCaddyfile;
use App\Tasks\UpdateCaddySiteImports;
use App\View\Components\SiteCaddyfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class InstallSiteCaddyfile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Site $site, public ?User $user = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $server = $this->site->server;

        $this->site->forceFill(['installed_at' => now()])->save();

        $server->runTask(new UpdateCaddyfile($this->site, SiteCaddyfile::build($this->site)))->throw()->asUser()->dispatch();

        $server->runTask(new UpdateCaddySiteImports($this->site->server->fresh()))->throw()->asRoot()->dispatch();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->site->forceFill(['installation_failed_at' => now()])->save();

        $this->site->server
            ->exceptionHandler()
            ->notify($this->user?->exists ? $this->user : $this->site->deployNotifiable())
            ->about($exception)
            ->withReference(__("Installation of site ':site'", ['site' => $this->site->address]))
            ->send();
    }
}
