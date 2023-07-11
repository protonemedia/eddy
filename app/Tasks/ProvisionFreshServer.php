<?php

namespace App\Tasks;

use App\Enum;
use App\Infrastructure\Entities\ServerStatus;
use App\Jobs\CleanupFailedServerProvisioning;
use App\Jobs\InstallTaskCleanupCron;
use App\Jobs\UpdateUserPublicKey;
use App\Mail\ServerProvisioned;
use App\Models\FirewallRule;
use App\Models\Server;
use App\Models\Task as TaskModel;
use App\Rules\PublicKey;
use App\Server\Firewall\RuleAction;
use App\Server\ProvisionStep;
use App\Server\Software;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProvisionFreshServer extends Task implements HasCallbacks
{
    protected int $timeout = 15 * 60;

    public function __construct(public Server $server, public ?Collection $sshKeys = null)
    {
        $this->sshKeys ??= new Collection;
    }

    protected function onFailed(TaskModel $task, Request $request)
    {
        dispatch(new CleanupFailedServerProvisioning($this->server, $this->taskModel));
    }

    protected function onFinished(TaskModel $task, Request $request)
    {
        // Store default firewall rules in the database...
        $this->server->firewallRules()->createMany([
            ['name' => 'SSH', 'port' => 22, 'action' => RuleAction::Allow],
            ['name' => 'HTTP', 'port' => 80, 'action' => RuleAction::Allow],
            ['name' => 'HTTPS', 'port' => 443, 'action' => RuleAction::Allow],
        ])->each(function (FirewallRule $firewallRule) {
            $firewallRule->forceFill(['installed_at' => now()])->save();
        });

        $this->server->forceFill([
            'provisioned_at' => now(),
            'status' => ServerStatus::Running,
        ])->save();

        dispatch(new InstallTaskCleanupCron($this->server));
        dispatch(new UpdateUserPublicKey($this->server));

        Mail::to($this->server->createdByUser)->queue(new ServerProvisioned($this->server));
    }

    protected function onCustomCallback(TaskModel $task, Request $request)
    {
        $request->validate([
            'provision_step_completed' => ['nullable', Enum::rule(ProvisionStep::class)],
            'software_installed' => ['nullable', Enum::rule(Software::class)],
            'public_key' => ['nullable', 'string', new PublicKey],
        ]);

        if ($provisionStep = $request->input('provision_step_completed')) {
            $this->server->completed_provision_steps[] = $provisionStep;
        }

        if ($software = $request->input('software_installed')) {
            $this->server->installed_software[] = $software;
        }

        if ($publicKey = $request->input('public_key')) {
            $this->server->user_public_key = $publicKey;
        }

        $this->server->save();
    }

    public function swapInMegabytes(): int
    {
        return match (true) {
            $this->server->memory_in_mb <= 2048 => 1024,
            $this->server->memory_in_mb <= 4096 => 2048,
            $this->server->memory_in_mb <= 8192 => 3072,

            default => 4096
        };
    }

    public function swappiness(): int
    {
        return match (true) {
            $this->server->memory_in_mb <= 1024 => 20,
            $this->server->memory_in_mb <= 2048 => 35,
            $this->server->memory_in_mb <= 4096 => 50,

            default => 60
        };
    }

    public function mysqlMaxConnections(): int
    {
        return match (true) {
            $this->server->memory_in_mb <= 1024 => 100,
            $this->server->memory_in_mb <= 2048 => 200,
            $this->server->memory_in_mb <= 4096 => 400,

            default => 500
        };
    }

    public function maxChildrenPhpPool(): int
    {
        $gigabytes = max(1, floor($this->server->memory_in_mb / 1024) - 1);

        return (int) ceil($gigabytes * 5 * 0.9);
    }

    public function provisionSteps(): array
    {
        return ProvisionStep::forFreshServer();
    }

    public function softwareStack(): array
    {
        return Software::defaultStack();
    }
}
