<?php

namespace Tests\Unit\Jobs;

use App\Jobs\RemoveSshKeyFromServer;
use App\Tasks\DeauthorizePublicKey;
use Database\Factories\ServerFactory;
use Database\Factories\SshKeyFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class RemoveSshKeyFromServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_adds_the_public_key()
    {
        TaskRunner::fake();

        $server = ServerFactory::new()->create();
        $sshKey = SshKeyFactory::new()->create();

        $job = new RemoveSshKeyFromServer($sshKey->public_key, $server);
        $job->handle();

        TaskRunner::assertDispatched(DeauthorizePublicKey::class, function ($task) use ($sshKey) {
            return $task->task->publicKey === $sshKey->public_key;
        });
    }
}
