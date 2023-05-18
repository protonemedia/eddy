<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UninstallDatabase;
use App\Models\Database;
use App\Notifications\JobOnServerFailed;
use App\Tasks\MySql\DropDatabase;
use Database\Factories\DatabaseFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UninstallDatabaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_drops_the_database()
    {
        TaskRunner::fake();

        /** @var Database */
        $database = DatabaseFactory::new()->create();

        $job = new UninstallDatabase($database);
        $job->handle();

        $this->assertNull($database->fresh());

        TaskRunner::assertDispatched(function (DropDatabase $task) use ($database) {
            return $task->name === $database->name;
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $database = DatabaseFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new UninstallDatabase($database, $user);
        $job->failed(new Exception('Uninstallation failed.'));

        $this->assertDatabaseHas('databases', [
            'name' => $database->name,
            'uninstallation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($database) {
            return $notification->server->is($database->server);
        });
    }
}
