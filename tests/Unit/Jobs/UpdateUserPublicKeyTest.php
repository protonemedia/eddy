<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UpdateUserPublicKey;
use App\Tasks\GetFile;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class UpdateUserPublicKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fetches_the_user_public_key()
    {
        TaskRunner::fake([
            GetFile::class => 'public-key',
        ]);

        $server = ServerFactory::new()->create();

        $job = new UpdateUserPublicKey($server);
        $job->handle();

        TaskRunner::assertDispatched(function (GetFile $task) {
            return $task->render() === 'tail -c 1M /home/eddy/.ssh/id_rsa.pub';
        });

        $this->assertEquals('public-key', $server->fresh()->user_public_key);
    }
}
