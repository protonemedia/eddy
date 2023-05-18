<?php

namespace Tests\Unit\Jobs;

use App\Jobs\MakeSoftwareDefaultOnServer;
use App\Server\Software;
use App\Tasks\UpdateAlternatives;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class MakeSoftwareDefaultOnServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calls_the_command_from_the_software_enum()
    {
        TaskRunner::fake();

        $server = ServerFactory::new()->create();

        $job = new MakeSoftwareDefaultOnServer($server, Software::Php81);
        $job->handle();

        TaskRunner::assertDispatched(UpdateAlternatives::class);
    }
}
