<?php

namespace Tests\Unit\Policies;

use App\Policies\DiskPolicy;
use Database\Factories\DiskFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $disk = DiskFactory::new()->forUser($user)->create();

        $policy = new DiskPolicy;

        $this->assertTrue($policy->viewAny($user, $disk));
        $this->assertTrue($policy->create($user, $disk));
        $this->assertTrue($policy->view($user, $disk));
        $this->assertTrue($policy->update($user, $disk));
        $this->assertTrue($policy->delete($user, $disk));
    }

    /** @test */
    public function it_allows_viewing_for_team_members()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $otherUser = UserFactory::new()->create();
        $user->currentTeam->users()->attach($otherUser);
        $disk = DiskFactory::new()->forUser($user)->create();

        $policy = new DiskPolicy;

        $this->assertTrue($policy->viewAny($otherUser, $disk));
        $this->assertTrue($policy->create($otherUser, $disk));
        $this->assertTrue($policy->view($otherUser, $disk));
        $this->assertFalse($policy->update($otherUser, $disk));
        $this->assertFalse($policy->delete($otherUser, $disk));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $disk = DiskFactory::new()->create();
        $otherUser = UserFactory::new()->withPersonalTeam()->create();

        $policy = new DiskPolicy;

        $this->assertFalse($policy->view($otherUser, $disk));
        $this->assertFalse($policy->update($otherUser, $disk));
        $this->assertFalse($policy->delete($otherUser, $disk));
    }
}
