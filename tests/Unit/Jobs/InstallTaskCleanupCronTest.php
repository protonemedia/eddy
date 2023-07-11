<?php

namespace Tests\Unit\Jobs;

use App\Jobs\InstallTaskCleanupCron;
use App\Models\Server;
use App\Tasks\UploadFile;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class InstallTaskCleanupCronTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uploads_the_cron_file()
    {
        TaskRunner::fake();

        /** @var Server */
        $server = ServerFactory::new()->create();

        $job = new InstallTaskCleanupCron($server);
        $job->handle();

        TaskRunner::assertDispatched(function (UploadFile $task) {
            $this->assertMatchesSnapshot($task->contents);

            return $task->path === '/etc/cron.d/eddy-tasks-cleanup';
        });
    }
}
