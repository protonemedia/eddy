<?php

namespace Tests\Unit\Jobs;

use App\Infrastructure\ProviderFactory;
use App\Jobs\DeleteServerFromInfrastructure;
use App\Notifications\ServerDeletionFailed;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeleteServerFromInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_deletes_the_server_from_the_provider()
    {
        $server = ServerFactory::new()->provisioned()->create();

        $provider = $this->mock(ProviderFactory::class);
        $provider->shouldReceive('forServer->deleteServer')->with(1);

        $job = new DeleteServerFromInfrastructure($server, $server->createdByUser);
        $job->handle();

        $this->assertNull($server->fresh());
    }

    /** @test */
    public function it_sends_a_notification_if_it_fails_to_delete_the_server()
    {
        Notification::fake();

        $server = ServerFactory::new()->provisioned()->create();

        $job = new DeleteServerFromInfrastructure($server, $server->createdByUser);
        $job->failed();

        $this->assertNull($server->fresh());

        Notification::assertSentTo($server->createdByUser, ServerDeletionFailed::class);
    }
}
