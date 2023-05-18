<?php

namespace Tests\Unit\Jobs;

use App\Jobs\RestartSoftwareOnServer;
use App\Server\Software;
use App\Tasks\RestartMySql;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class RestartSoftwareOnServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calls_the_command_from_the_software_enum()
    {
        TaskRunner::fake();

        $server = ServerFactory::new()->create();

        $job = new RestartSoftwareOnServer($server, Software::MySql80);
        $job->handle();

        TaskRunner::assertDispatched(RestartMySql::class);
    }
}
