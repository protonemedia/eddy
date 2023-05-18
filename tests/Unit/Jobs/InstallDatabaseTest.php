<?php

namespace Tests\Unit\Jobs;

use App\Jobs\InstallDatabase;
use App\Notifications\JobOnServerFailed;
use App\Tasks\MySql\CreateDatabase;
use App\Tasks\MySql\DropDatabase;
use Database\Factories\DatabaseFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class InstallDatabaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_executes_the_query()
    {
        TaskRunner::fake();

        /** @var Database */
        $database = DatabaseFactory::new()->create();

        $job = new InstallDatabase($database);
        $job->handle();

        $this->assertNotNull($database->fresh()->installed_at);

        TaskRunner::assertDispatched(function (CreateDatabase $task) use ($database) {
            return $task->getHosts() === [$database->server->public_ipv4, '%']
                && $task->name === $database->name;
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();
        TaskRunner::fake();

        $database = DatabaseFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new InstallDatabase($database, $user);
        $job->failed(new Exception('Installation failed.'));

        $this->assertDatabaseHas('databases', [
            'id' => $database->id,
            'installation_failed_at' => now(),
        ]);

        TaskRunner::assertDispatched(function (DropDatabase $task) use ($database) {
            return $task->name === $database->name;
        });

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($database) {
            return $notification->server->is($database->server);
        });
    }
}
