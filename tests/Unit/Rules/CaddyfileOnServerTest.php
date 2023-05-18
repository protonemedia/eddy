<?php

namespace Tests\Unit\Rules;

use App\Rules\CaddyfileOnServer;
use App\Tasks\ValidateCaddyfile;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class CaddyfileOnServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_the_caddyfile()
    {
        TaskRunner::fake([
            ValidateCaddyfile::class => 'Valid configuration',
        ]);

        $server = ServerFactory::new()->create();

        $rule = new CaddyfileOnServer($server);
        $validator = Validator::make(
            ['caddyfile' => 'valid'],
            ['caddyfile' => $rule]
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_invalidates_the_caddyfile()
    {
        TaskRunner::fake([
            ValidateCaddyfile::class => 'Invalid configuration',
        ]);

        $server = ServerFactory::new()->create();

        $rule = new CaddyfileOnServer($server);
        $validator = Validator::make(
            ['caddyfile' => 'valid'],
            ['caddyfile' => $rule]
        );

        $this->assertTrue($validator->fails());
    }
}
