<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UninstallFirewallRule;
use App\Models\FirewallRule;
use App\Notifications\JobOnServerFailed;
use App\Tasks\DeleteFirewallRule;
use Database\Factories\FirewallRuleFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UninstallFirewallRuleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_executes_a_command_to_delete_the_firewall_rule()
    {
        TaskRunner::fake();

        /** @var FirewallRule */
        $rule = FirewallRuleFactory::new()->create();

        $job = new UninstallFirewallRule($rule);
        $job->handle();

        $this->assertNull($rule->fresh());

        TaskRunner::assertDispatched(DeleteFirewallRule::class);
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $user = UserFactory::new()->create();
        $rule = FirewallRuleFactory::new()->create();

        $job = new UninstallFirewallRule($rule, $user);
        $job->failed(new Exception('Uninstallation failed.'));

        $this->assertDatabaseHas('firewall_rules', [
            'id' => $rule->id,
            'uninstallation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($rule) {
            return $notification->server->is($rule->server);
        });
    }
}
