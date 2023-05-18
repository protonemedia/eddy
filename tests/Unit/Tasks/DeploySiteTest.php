<?php

namespace Tests\Unit\Tasks;

use App\Events\DeploymentUpdated;
use App\Jobs\InstallSiteCaddyfile;
use App\Jobs\InstallWordpressCron;
use App\Models\DeploymentStatus;
use App\Models\Site;
use App\Models\SiteType;
use App\Models\Task;
use App\Notifications\DeploymentFailed;
use App\Tasks\CallbackType;
use App\Tasks\DeploySite;
use Database\Factories\DeploymentFactory;
use Database\Factories\Dummies;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeploySiteTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = SiteFactory::new()->forRepository('git@github.com:protonemedia/php-app.dev.git')->create([
            'zero_downtime_deployment' => false,
            'address' => 'app.com',
            'user' => 'protone',
        ]);
    }

    /** @test */
    public function it_has_a_default_deploy_script()
    {
        $deployment = $this->site->deployments()->create([
            'status' => DeploymentStatus::Pending,
        ]);

        $task = new DeploySite($deployment);
        $task->setTaskModel(new Task(['id' => 'id']));

        $script = $task->getScript();

        $this->assertMatchesBashSnapshot($script);
    }

    /** @test */
    public function it_can_add_hooks_to_the_deployment_script()
    {
        $this->site->update([
            'hook_before_updating_repository' => 'echo "before updating repository"',
            'hook_after_updating_repository' => 'echo "after updating repository"',
        ]);

        $deployment = $this->site->deployments()->create([
            'status' => DeploymentStatus::Pending,
        ]);

        $task = new DeploySite($deployment);
        $task->setTaskModel(new Task(['id' => 'id']));

        $script = $task->getScript();

        $this->assertMatchesBashSnapshot($script);
    }

    /** @test */
    public function it_can_use_a_deploy_key()
    {
        $keyPair = Dummies::ed25519KeyPair();

        $this->site->forceFill([
            'deploy_key_public' => $keyPair->publicKey,
            'deploy_key_private' => $keyPair->privateKey,
        ])->save();

        $deployment = $this->site->deployments()->create([
            'status' => DeploymentStatus::Pending,
        ]);

        $task = new DeploySite($deployment);
        $task->setTaskModel(new Task(['id' => 'id']));

        $script = $task->getScript();

        $this->assertMatchesBashSnapshot($script);
    }

    /** @test */
    public function it_can_act_on_output_updated()
    {
        $deployment = DeploymentFactory::new()->create();
        $deploySite = new DeploySite($deployment);

        Event::fake([DeploymentUpdated::class]);

        $deploySite->onOutputUpdated('Some output.');

        Event::assertDispatched(DeploymentUpdated::class, function ($event) use ($deployment) {
            return $event->deployment->id === $deployment->id;
        });
    }

    /** @test */
    public function it_can_act_on_timeout()
    {
        Notification::fake();

        $deployment = DeploymentFactory::new()->create();
        $deploySite = new DeploySite($deployment);

        $request = Request::create('/');

        $deploySite->handleCallback($deployment->task, $request, CallbackType::Timeout);

        $deployment->refresh();

        $this->assertEquals(DeploymentStatus::Timeout, $deployment->status);

        Notification::assertSentTo($deployment->user, DeploymentFailed::class);
    }

    /** @test */
    public function it_can_act_on_failed()
    {
        Notification::fake();

        $deployment = DeploymentFactory::new()->create();
        $deploySite = new DeploySite($deployment);

        $request = Request::create('/');

        $deploySite->handleCallback($deployment->task, $request, CallbackType::Failed);

        $deployment->refresh();

        $this->assertEquals(DeploymentStatus::Failed, $deployment->status);

        Notification::assertSentTo($deployment->user, DeploymentFailed::class);
    }

    /** @test */
    public function it_can_act_on_finished()
    {
        Notification::fake();
        Bus::fake();

        $deployment = DeploymentFactory::new()->create();
        $deploySite = new DeploySite($deployment);

        $request = Request::create('/');

        $deploySite->handleCallback($deployment->task, $request, CallbackType::Finished);
        $deployment->refresh();

        $this->assertEquals(DeploymentStatus::Finished, $deployment->status);

        Notification::assertNothingSent();
        Bus::assertDispatched(InstallSiteCaddyfile::class);
    }

    /** @test */
    public function it_can_install_a_wordpress_cron()
    {
        Notification::fake();
        Bus::fake();

        $deployment = DeploymentFactory::new()->create();
        $deployment->site->update(['type' => SiteType::Wordpress]);
        $deploySite = new DeploySite($deployment);

        $request = Request::create('/');

        $deploySite->handleCallback($deployment->task, $request, CallbackType::Finished);
        $deployment->refresh();

        $this->assertEquals(DeploymentStatus::Finished, $deployment->status);

        Notification::assertNothingSent();
        Bus::assertDispatched(InstallSiteCaddyfile::class);
        Bus::assertDispatched(InstallWordpressCron::class);
    }

    /** @test */
    public function it_can_act_on_custom_callback()
    {
        $deployment = DeploymentFactory::new()->create();
        $deploySite = new DeploySite($deployment);

        $request = Request::create('/', 'POST', [
            'git_hash' => $hash = fake()->sha1,
        ]);

        $deploySite->handleCallback($deployment->task, $request, CallbackType::Custom);
        $deployment->refresh();

        $this->assertEquals($hash, $deployment->git_hash);
    }
}
