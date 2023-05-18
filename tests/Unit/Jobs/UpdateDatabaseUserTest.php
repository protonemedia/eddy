<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UpdateDatabaseUser;
use App\Models\Database;
use App\Models\DatabaseUser;
use App\Notifications\JobOnServerFailed;
use App\Tasks\MySql\GrantAllPrivileges;
use App\Tasks\MySql\RevokeAllPrivileges;
use App\Tasks\MySql\UpdateUserPassword;
use Database\Factories\DatabaseFactory;
use Database\Factories\DatabaseUserFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UpdateDatabaseUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_the_user_password()
    {
        TaskRunner::fake();

        /** @var DatabaseUser */
        $databaseUser = DatabaseUserFactory::new()->create();

        $job = new UpdateDatabaseUser($databaseUser, 'new-password');
        $job->handle();

        TaskRunner::assertDispatched(function (UpdateUserPassword $task) use ($databaseUser) {
            return $task->name === $databaseUser->name
                && $task->password === 'new-password';
        });
    }

    /** @test */
    public function it_manages_the_privileges()
    {
        TaskRunner::fake();

        /** @var DatabaseUser */
        $databaseUser = DatabaseUserFactory::new()->create();

        /** @var Database */
        $databaseA = DatabaseFactory::new()->forServer($databaseUser->server)->create(['name' => 'database_a']);

        /** @var Database */
        $databaseB = DatabaseFactory::new()->forServer($databaseUser->server)->create(['name' => 'database_b']);

        $databaseUser->databases()->attach($databaseA);

        $job = new UpdateDatabaseUser($databaseUser);
        $job->handle();

        TaskRunner::assertDispatched(function (GrantAllPrivileges $task) use ($databaseUser) {
            return $task->name === $databaseUser->name
                && $task->database === 'database_a';
        });

        TaskRunner::assertDispatched(function (RevokeAllPrivileges $task) use ($databaseUser) {
            return $task->name === $databaseUser->name
                && $task->database === 'database_b';
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $database = DatabaseUserFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new UpdateDatabaseUser($database, user: $user);
        $job->failed(new Exception('Update failed.'));

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($database) {
            return $notification->server->is($database->server);
        });
    }
}
