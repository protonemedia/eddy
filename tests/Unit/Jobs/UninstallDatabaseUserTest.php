<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UninstallDatabaseUser;
use App\Models\DatabaseUser;
use App\Notifications\JobOnServerFailed;
use App\Tasks\MySql\DropUser;
use Database\Factories\DatabaseUserFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UninstallDatabaseUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_drops_the_database()
    {
        TaskRunner::fake();

        /** @var DatabaseUser */
        $databaseUser = DatabaseUserFactory::new()->create();

        $job = new UninstallDatabaseUser($databaseUser);
        $job->handle();

        $this->assertNull($databaseUser->fresh());

        TaskRunner::assertDispatched(function (DropUser $task) use ($databaseUser) {
            return $task->user === $databaseUser->name;
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $database = DatabaseUserFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new UninstallDatabaseUser($database, $user);
        $job->failed(new Exception('Uninstallation failed.'));

        $this->assertDatabaseHas('database_users', [
            'name' => $database->name,
            'uninstallation_failed_at' => now(),
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($database) {
            return $notification->server->is($database->server);
        });
    }
}
