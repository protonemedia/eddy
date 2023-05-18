<?php

namespace Tests\Unit\Jobs;

use App\Jobs\InstallFirewallRule;
use App\Models\FirewallRule;
use App\Notifications\JobOnServerFailed;
use App\Tasks\AddFirewallRule;
use Database\Factories\FirewallRuleFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class InstallFirewallRuleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uploads_the_supervisor_config_and_reloads_supervisor()
    {
        TaskRunner::fake();

        /** @var FirewallRule */
        $rule = FirewallRuleFactory::new()->create();

        $job = new InstallFirewallRule($rule);
        $job->handle();

        $this->assertNotNull($rule->fresh()->installed_at);

        TaskRunner::assertDispatched(AddFirewallRule::class);
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $user = UserFactory::new()->create();
        $rule = FirewallRuleFactory::new()->create();

        $job = new InstallFirewallRule($rule, $user);
        $job->failed(new Exception('Installation failed.'));

        $this->assertDatabaseHas('firewall_rules', [
            'id' => $rule->id,
            'installation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($rule) {
            return $notification->server->is($rule->server);
        });
    }
}
