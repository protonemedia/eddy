<?php

namespace Tests\Unit\Jobs;

use App\Jobs\AddServerSshKeyToGithub;
use App\Jobs\UpdateUserPublicKey;
use App\Tasks\GetFile;
use Database\Factories\CredentialsFactory;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
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

    /** @test */
    public function it_can_dispatch_a_job_to_add_the_public_key_to_github()
    {
        TaskRunner::fake();
        Bus::fake();

        $server = ServerFactory::new()->create([
            'github_credentials_id' => $githubCredentials = CredentialsFactory::new()->github()->create(),
        ]);

        $job = new UpdateUserPublicKey($server);
        $job->handle();

        Bus::assertDispatched(AddServerSshKeyToGithub::class, function (AddServerSshKeyToGithub $job) use ($server, $githubCredentials) {
            return $job->server->is($server) && $job->githubCredentials->is($githubCredentials);
        });
    }
}
