<?php

namespace App\Jobs;

use App\Models\FirewallRule;
use App\Models\User;
use App\Tasks\DeleteFirewallRule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UninstallFirewallRule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public FirewallRule $rule, public ?User $user = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->rule->server->runTask(new DeleteFirewallRule($this->rule))->asRoot()->dispatch();

        $this->rule->delete();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->rule->forceFill(['uninstallation_failed_at' => now()])->save();

        $this->rule->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__("Uninstallation of firewall rule ':rule'", ['rule' => "`{$this->rule->formatAsUfwRule()}`"]))
            ->send();
    }
}
