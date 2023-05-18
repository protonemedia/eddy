<?php

namespace Tests\Unit\Jobs;

use App\Jobs\AddSshKeyToServer;
use App\Tasks\AuthorizePublicKey;
use Database\Factories\ServerFactory;
use Database\Factories\SshKeyFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class AddSshKeyToServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_adds_the_public_key()
    {
        TaskRunner::fake();

        $server = ServerFactory::new()->create();
        $sshKey = SshKeyFactory::new()->create();

        $job = new AddSshKeyToServer($sshKey, $server);
        $job->handle();

        TaskRunner::assertDispatched(AuthorizePublicKey::class, function ($task) use ($sshKey) {
            return $task->task->publicKey === $sshKey->public_key;
        });
    }
}
