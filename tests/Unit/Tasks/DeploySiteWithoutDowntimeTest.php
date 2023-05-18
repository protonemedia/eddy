<?php

namespace Tests\Unit\Tasks;

use App\Models\DeploymentStatus;
use App\Models\Site;
use App\Models\Task;
use App\Tasks\DeploySiteWithoutDowntime;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DeploySiteWithoutDowntimeTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        // Release directory is based on created_at timestamp
        Carbon::setTestNow('2022-01-01 12:00:00');

        $this->site = SiteFactory::new()->forRepository('git@github.com:protonemedia/php-app.dev.git')->create([
            'address' => 'app.com',
            'user' => 'protone',
            'shared_directories' => ['storage'],
            'shared_files' => ['.env'],
            'writeable_directories' => ['bootstrap/cache'],
        ]);
    }

    /** @test */
    public function it_has_a_default_deploy_script()
    {
        $deployment = $this->site->deployments()->create([
            'status' => DeploymentStatus::Pending,
        ]);

        $task = new DeploySiteWithoutDowntime($deployment);
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
            'hook_before_making_current' => 'echo "before making current"',
            'hook_after_making_current' => 'echo "after making current"',
        ]);

        $deployment = $this->site->deployments()->create([
            'status' => DeploymentStatus::Pending,
        ]);

        $task = new DeploySiteWithoutDowntime($deployment);
        $task->setTaskModel(new Task(['id' => 'id']));

        $script = $task->getScript();

        $this->assertMatchesBashSnapshot($script);
    }
}
