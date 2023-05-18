<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CleanupFailedServerProvisioning;
use App\Models\Task;
use App\Notifications\ServerProvisioningFailed;
use Database\Factories\ServerFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CleanupFailedServerProvisioningTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_deletes_server_on_handle()
    {
        $server = ServerFactory::new()->create();

        (new CleanupFailedServerProvisioning($server))->handle();

        $this->assertDatabaseMissing('servers', ['id' => $server->id]);
    }

    /** @test */
    public function it_fetches_the_latest_task_output_and_sends_it_to_the_user()
    {
        Notification::fake();

        $server = ServerFactory::new()->create();

        // Mock the updateOutputWithoutCallbacks method to ensure it's called
        $mockTask = $this->mock(Task::class);
        $mockTask->shouldReceive('updateOutputWithoutCallbacks')->once();
        $mockTask->shouldReceive('tailOutput')->once()->andReturn('output');

        (new CleanupFailedServerProvisioning($server, $mockTask))->handle();

        Notification::assertSentTo(
            $server->createdByUser,
            ServerProvisioningFailed::class,
            function ($notification, $channels) {
                return $notification->output === 'output';
            }
        );
    }

    /** @test */
    public function it_notifies_the_user_about_the_failed_server_provisioning()
    {
        $user = UserFactory::new()->create();
        $server = ServerFactory::new()->create(['created_by_user_id' => $user->id]);

        Notification::fake();

        (new CleanupFailedServerProvisioning($server))->handle();

        Notification::assertSentTo($user, ServerProvisioningFailed::class, function ($notification) use ($server) {
            return $notification->serverName === $server->name && $notification->output === '';
        });
    }

    /** @test */
    public function it_notifies_user_about_failed_server_provisioning_with_task_output()
    {
        $user = UserFactory::new()->create();
        $server = ServerFactory::new()->create([
            'created_by_user_id' => $user->id,
        ]);

        $mockTask = $this->mock(Task::class);
        $mockTask->shouldReceive('updateOutputWithoutCallbacks')->once();
        $mockTask->shouldReceive('tailOutput')->once()->andReturn('Hey, this is the output');

        Notification::fake();

        (new CleanupFailedServerProvisioning($server, $mockTask))->handle();

        Notification::assertSentTo($user, ServerProvisioningFailed::class, function ($notification) use ($server) {
            return $notification->serverName === $server->name && $notification->output === 'Hey, this is the output';
        });
    }
}
