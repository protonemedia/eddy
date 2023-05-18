<?php

namespace Tests\Unit\Policies;

use App\Policies\CronPolicy;
use Database\Factories\CronFactory;
use Database\Factories\ServerFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CronTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();
        $cron = CronFactory::new()->forServer($server)->create();

        $policy = new CronPolicy;

        $this->assertTrue($policy->viewAny($user, $cron));
        $this->assertTrue($policy->create($user, $cron));
        $this->assertTrue($policy->view($user, $cron));
        $this->assertTrue($policy->update($user, $cron));
        $this->assertTrue($policy->delete($user, $cron));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $cron = CronFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new CronPolicy;

        $this->assertFalse($policy->view($otherUser, $cron));
        $this->assertFalse($policy->update($otherUser, $cron));
        $this->assertFalse($policy->delete($otherUser, $cron));
    }
}
