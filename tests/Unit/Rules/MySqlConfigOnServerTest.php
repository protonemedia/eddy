<?php

namespace Tests\Unit\Rules;

use App\Rules\MySqlConfigOnServer;
use App\Tasks\ValidateMysqlConfig;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class MySqlConfigOnServerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_the_mysql_config()
    {
        TaskRunner::fake([
            ValidateMysqlConfig::class => 'Valid configuration',
        ]);

        $server = ServerFactory::new()->create();

        $rule = new MySqlConfigOnServer($server);
        $validator = Validator::make(
            ['caddyfile' => 'valid'],
            ['caddyfile' => $rule]
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_invalidates_the_mysql_config()
    {
        TaskRunner::fake([
            ValidateMysqlConfig::class => '[ERROR]',
        ]);

        $server = ServerFactory::new()->create();

        $rule = new MySqlConfigOnServer($server);
        $validator = Validator::make(
            ['caddyfile' => 'valid'],
            ['caddyfile' => $rule]
        );

        $this->assertTrue($validator->fails());
    }
}
