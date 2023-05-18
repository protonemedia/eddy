<?php

namespace Tests\Unit\Jobs;

use App\Jobs\InstallDatabaseUser;
use App\Models\DatabaseUser;
use App\Notifications\JobOnServerFailed;
use App\Tasks\MySql\CreateUser;
use App\Tasks\MySql\DropUser;
use Database\Factories\DatabaseUserFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class InstallDatabaseUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_executes_the_query()
    {
        TaskRunner::fake();

        /** @var DatabaseUser */
        $databaseUser = DatabaseUserFactory::new()->create();

        $job = new InstallDatabaseUser($databaseUser, 'password');
        $job->handle();

        $this->assertNotNull($databaseUser->fresh()->installed_at);

        TaskRunner::assertDispatched(function (CreateUser $task) use ($databaseUser) {
            return $task->getHosts() === [$databaseUser->server->public_ipv4, '%']
                && $task->name === $databaseUser->name
                && $task->password === 'password';
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();
        TaskRunner::fake();

        $databaseUser = DatabaseUserFactory::new()->create();
        $user = UserFactory::new()->create();

        $job = new InstallDatabaseUser($databaseUser, 'password', $user);
        $job->failed(new Exception('Installation failed.'));

        $this->assertDatabaseHas('database_users', [
            'id' => $databaseUser->id,
            'installation_failed_at' => now(),
        ]);

        TaskRunner::assertDispatched(function (DropUser $task) use ($databaseUser) {
            return $task->user === $databaseUser->name;
        });

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($databaseUser) {
            return $notification->server->is($databaseUser->server);
        });
    }
}
