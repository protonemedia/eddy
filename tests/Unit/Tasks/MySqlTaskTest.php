<?php

namespace Tests\Unit\Tasks;

use App\Models\Server;
use App\Tasks\MySql\MySqlTask;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MySqlTaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_throws_an_exception_when_the_server_is_not_set()
    {
        $task = new class extends MySqlTask
        {
            public function sql(): string
            {
                return 'SHOW * FROM users';
            }
        };

        try {
            $task->render();
        } catch (Exception $e) {
            return $this->assertEquals('Forgot to set the user or password.', $e->getMessage());
        }

        $this->fail('Should have thrown an exception.');
    }

    /** @test */
    public function it_wraps_the_sql_query_into_a_command()
    {
        $task = new class extends MySqlTask
        {
            public function sql(): string
            {
                return 'SHOW * FROM users';
            }
        };

        $server = new Server;
        $server->public_ipv4 = '1.2.3.4';
        $server->database_password = 'secret';
        $server->exists = true;

        $command = $task->onServer($server)->render();

        $this->assertEquals("MYSQL_PWD=secret mysql --user=root --execute='SHOW * FROM users' --skip-column-names", $command);
    }
}
