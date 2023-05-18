<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifySubscriptionStatus;
use App\Models\Server;
use App\Models\User;
use App\Provider;
use App\TeamSubscriptionOptions;
use Database\Factories\ServerFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_forbids_the_user_from_adding_servers_when_it_reached_the_subscription_plan_limit()
    {
        /** @var User */
        $user = UserFactory::new()->withPersonalTeam()->create();

        $this->app->bind(TeamSubscriptionOptions::class, function () {
            return Mockery::mock(TeamSubscriptionOptions::class, function ($mock) {
                $mock->shouldReceive('canCreateServer')->andReturn(false);
            });
        });

        $this->actingAs($user)
            ->withoutMiddleware(VerifySubscriptionStatus::class)
            ->post(route('servers.store'))
            ->assertForbidden();
    }

    /** @test */
    public function it_provides_a_script_for_custom_servers_to_add_eddys_public_key()
    {
        /** @var Server */
        $server = ServerFactory::new()->provider(Provider::CustomServer)->create();

        $this->actingAs($server->createdByUser)
            ->get($server->provisionScriptUrl())
            ->assertSee('/root/.ssh/authorized_keys')
            ->assertSee('ssh-ed25519');
    }
}
