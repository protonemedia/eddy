<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\CredentialsPolicy;
use Database\Factories\CredentialsFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CredentialsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_everything_for_the_user()
    {
        $user = User::factory()->create();
        $credentials = CredentialsFactory::new()->digitalOcean()->forUser($user)->create();

        $policy = new CredentialsPolicy;

        $this->assertTrue($policy->viewAny($user, $credentials));
        $this->assertTrue($policy->create($user, $credentials));
        $this->assertTrue($policy->view($user, $credentials));
        $this->assertTrue($policy->update($user, $credentials));
        $this->assertTrue($policy->delete($user, $credentials));
    }

    /** @test */
    public function it_rejects_everything_for_another_user()
    {
        $credentials = CredentialsFactory::new()->digitalOcean()->create();
        $otherUser = User::factory()->create();

        $policy = new CredentialsPolicy;

        $this->assertFalse($policy->view($otherUser, $credentials));
        $this->assertFalse($policy->update($otherUser, $credentials));
        $this->assertFalse($policy->delete($otherUser, $credentials));
    }
}
