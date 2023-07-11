<?php

namespace Tests\Unit\Tasks;

use App\Infrastructure\Entities\ServerStatus;
use App\Jobs\InstallTaskCleanupCron;
use App\Jobs\UpdateUserPublicKey;
use App\Mail\ServerProvisioned;
use App\Models\Server;
use App\Models\Task;
use App\Tasks\ProvisionFreshServer;
use Database\Factories\ServerFactory;
use Database\Factories\SshKeyFactory;
use Database\Factories\TaskFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProvisionFreshServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_adds_the_default_firewall_rules_and_status_when_finished_and_sends_a_mail_and_fetches_the_user_public_key()
    {
        Bus::fake();
        Mail::fake();

        /** @var Server */
        $server = ServerFactory::new()->notProvisioned()->create();

        $task = new ProvisionFreshServer($server);
        invade($task)->onFinished(new Task, Request::createFromGlobals());

        $this->assertEquals(ServerStatus::Running, $server->fresh()->status);
        $this->assertEquals([22, 80, 443], $server->firewallRules->map->port->all());

        Bus::assertDispatched(function (InstallTaskCleanupCron $job) use ($server) {
            return $job->server->is($server);
        });

        Bus::assertDispatched(function (UpdateUserPublicKey $job) use ($server) {
            return $job->server->is($server);
        });

        Mail::assertQueued(ServerProvisioned::class, function ($mail) use ($server) {
            return $mail->hasTo($server->createdByUser->email);
        });
    }

    /** @test */
    public function it_builds_a_provision_script_with_the_default_stack()
    {
        /** @var Server */
        $server = ServerFactory::new()->notProvisioned()->create();

        $taskModel = TaskFactory::new()->forServer($server)->create();
        $taskModel->id = 1;

        $task = new ProvisionFreshServer($server);
        $task->setTaskModel($taskModel);
        $script = $task->getScript();

        $this->assertMatchesBashSnapshot($script);
    }

    /** @test */
    public function it_builds_a_provision_script_with_additional_ssh_keys()
    {
        /** @var Server */
        $server = ServerFactory::new()->notProvisioned()->create();

        $taskModel = TaskFactory::new()->forServer($server)->create();
        $taskModel->id = 1;

        $sshKeys = SshKeyFactory::new()->count(2)->create();

        $task = new ProvisionFreshServer($server, $sshKeys);
        $task->setTaskModel($taskModel);
        $script = $task->getScript();

        $this->assertMatchesBashSnapshot($script);
    }
}
