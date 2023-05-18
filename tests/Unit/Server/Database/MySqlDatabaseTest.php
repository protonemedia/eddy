<?php

namespace Tests\Unit\Server\Database;

use App\Models\Server;
use App\Server\Database\CouldNotAuthenticateWithDatabaseException;
use App\Server\Database\CouldNotConnectToDatabaseException;
use App\Server\Database\CouldNotCreateUserException;
use App\Server\Database\CouldNotDropUserException;
use App\Server\Database\CouldNotGrantPrivilegesException;
use App\Server\Database\DatabaseAlreadyExistsException;
use App\Server\Database\DatabaseNotFoundException;
use App\Server\Database\MySqlDatabase;
use App\Tasks\MySql\CreateDatabase;
use App\Tasks\MySql\CreateUser;
use App\Tasks\MySql\DropDatabase;
use App\Tasks\MySql\DropUser;
use App\Tasks\MySql\GetDatabases;
use App\Tasks\MySql\GetTables;
use App\Tasks\MySql\GetUsers;
use App\Tasks\MySql\GrantAllPrivileges;
use App\Tasks\MySql\MySqlTask;
use App\Tasks\MySql\RevokeAllPrivileges;
use App\Tasks\MySql\UpdateUserPassword;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use ProtoneMedia\LaravelTaskRunner\PendingTask;
use ProtoneMedia\LaravelTaskRunner\ProcessOutput;
use Tests\TestCase;

class MySqlDatabaseTest extends TestCase
{
    private MySqlDatabase $manager;

    public function setUp(): void
    {
        parent::setUp();

        $this->server = (new Server)->forceFill([
            'public_ipv4' => '1.2.3.4',
            'ssh_port' => '22',
            'username' => 'protone',
            'private_key' => 'secret',
            'database_password' => 'secret',
        ]);

        $this->manager = new MySqlDatabase($this->server);

        TaskRunner::fake()->preventStrayTasks();
    }

    /** @test */
    public function it_doesnt_wrap_wildcard_values()
    {
        $this->assertEquals('*', MySqlTask::wrapValue('*'));
    }

    /** @test */
    public function it_throws_when_it_cant_execute_the_command_at_all()
    {
        TaskRunner::fake([
            GetDatabases::class => ProcessOutput::make('')->setExitCode(1),
        ]);

        try {
            $this->manager->getDatabases();
        } catch (CouldNotConnectToDatabaseException $e) {
            return $this->assertEquals('Could not connect to the database.', $e->getMessage());
        }

        $this->fail('CouldNotConnectToDatabaseException was not thrown.');
    }

    /** @test */
    public function it_throws_an_exception_when_it_cant_connect()
    {
        TaskRunner::fake([
            GetDatabases::class => ProcessOutput::make('ERROR 2002 (HY000): Can\'t connect to local MySQL server through socket')->setExitCode(0),
        ]);

        try {
            $this->manager->getDatabases();
        } catch (CouldNotConnectToDatabaseException $e) {
            return $this->assertEquals('ERROR 2002 (HY000): Can\'t connect to local MySQL server through socket', $e->getMessage());
        }

        $this->fail('CouldNotConnectToDatabaseException was not thrown.');
    }

    /** @test */
    public function it_throws_an_exception_when_it_has_no_access()
    {
        TaskRunner::fake([
            GetDatabases::class => ProcessOutput::make('ERROR 1045 (28000): Access denied for user \'protone\'@\'localhost\' (using password: YES)')->setExitCode(0),
        ]);

        try {
            $this->manager->getDatabases();
        } catch (CouldNotAuthenticateWithDatabaseException $e) {
            return $this->assertEquals('ERROR 1045 (28000): Access denied for user \'protone\'@\'localhost\' (using password: YES)', $e->getMessage());
        }

        $this->fail('CouldNotAuthenticateWithDatabaseException was not thrown.');
    }

    /** @test */
    public function it_can_fetch_all_databases()
    {
        $this->assertEquals('SHOW DATABASES;', (new GetDatabases)->sql());

        TaskRunner::fake([
            GetDatabases::class => '
information_schema
mysql
performance_schema
protone
sys
',
        ]);

        $databases = $this->manager->getDatabases();

        $this->assertEquals(['protone'], $databases);
    }

    /** @test */
    public function it_can_fetch_all_users()
    {
        $this->assertEquals('SELECT host, user FROM mysql.user;', (new GetUsers)->sql());

        TaskRunner::fake([
            GetUsers::class => "
%\tprotone
%\troot
192.168.60.61\tprotone
localhost\tmysql.infoschema
localhost\tmysql.session
localhost\tmysql.sys
",
        ]);

        $users = $this->manager->getUsers();

        $this->assertCount(3, $users);

        $this->assertEquals('protone', $users[0]->user);
        $this->assertEquals('%', $users[0]->host);

        $this->assertEquals('protone', $users[1]->user);
        $this->assertEquals('192.168.60.61', $users[1]->host);

        $this->assertEquals('root', $users[2]->user);
        $this->assertEquals('%', $users[2]->host);
    }

    /** @test */
    public function it_can_fetch_the_tables_of_a_database()
    {
        TaskRunner::fake([
            GetTables::class => '
table1
table2
',
        ]);

        $databases = $this->manager->getTables('protone');

        $this->assertEquals(['table1', 'table2'], $databases);

        TaskRunner::assertDispatched(GetTables::class, function (PendingTask $task) {
            $this->assertEquals('USE protone; SHOW TABLES;', $task->task->sql());

            return true;
        });
    }

    /** @test */
    public function it_can_create_a_user_on_a_host()
    {
        TaskRunner::fake(CreateUser::class);

        $this->manager->createUser('protone', 'secret');

        TaskRunner::assertDispatched(CreateUser::class, function (PendingTask $task) {
            $this->assertEquals('CREATE USER IF NOT EXISTS `protone`@`1.2.3.4` IDENTIFIED BY "secret"; CREATE USER IF NOT EXISTS `protone`@`%` IDENTIFIED BY "secret";', $task->task->sql());

            return true;
        });
    }

    /** @test */
    public function it_can_update_a_user_password()
    {
        TaskRunner::fake(UpdateUserPassword::class);

        $this->manager->updateUserPassword('protone', 'secret');

        TaskRunner::assertDispatched(UpdateUserPassword::class, function (PendingTask $task) {
            $this->assertEquals('ALTER USER `protone`@`1.2.3.4` IDENTIFIED BY "secret"; ALTER USER `protone`@`%` IDENTIFIED BY "secret"; FLUSH PRIVILEGES;', $task->task->sql());

            return true;
        });
    }

    /** @test */
    public function it_throws_an_exception_when_it_cant_create_the_user()
    {
        TaskRunner::fake([
            CreateUser::class => ProcessOutput::make('ERROR 1396 (HY000): Operation CREATE USER failed for \'protone\'@\'%\'')->setExitCode(0),
        ]);

        try {
            $this->manager->createUser('protone', 'secret');
        } catch (CouldNotCreateUserException $e) {
            return $this->assertEquals('ERROR 1396 (HY000): Operation CREATE USER failed for \'protone\'@\'%\'', $e->getMessage());
        }

        $this->fail('CouldNotCreateUserException was not thrown.');
    }

    /** @test */
    public function it_can_grant_all_privileges()
    {
        TaskRunner::fake(GrantAllPrivileges::class);

        $this->manager->grantAllPrivileges('protone', 'app');

        TaskRunner::assertDispatched(GrantAllPrivileges::class, function (PendingTask $task) {
            $this->assertEquals('GRANT ALL ON `app`.* TO `protone`@`1.2.3.4` WITH GRANT OPTION; GRANT ALL ON `app`.* TO `protone`@`%` WITH GRANT OPTION; FLUSH PRIVILEGES;', $task->task->sql());

            return true;
        });
    }

    /** @test */
    public function it_can_revoke_all_privileges()
    {
        TaskRunner::fake(RevokeAllPrivileges::class);

        $this->manager->revokeAllPrivileges('protone', 'app');

        TaskRunner::assertDispatched(RevokeAllPrivileges::class, function (PendingTask $task) {
            $this->assertEquals('REVOKE ALL ON `app`.* FROM `protone`@`1.2.3.4`; REVOKE ALL ON `app`.* FROM `protone`@`%`; FLUSH PRIVILEGES;', $task->task->sql());

            return true;
        });
    }

    /** @test */
    public function it_throws_an_exception_when_the_privileges_cant_be_granted()
    {
        TaskRunner::fake([
            GrantAllPrivileges::class => ProcessOutput::make('ERROR 1410 (42000): You are not allowed to create a user with GRANT')->setExitCode(0),
        ]);

        try {
            $this->manager->grantAllPrivileges('protone', 'app');
        } catch (CouldNotGrantPrivilegesException $e) {
            return $this->assertEquals('ERROR 1410 (42000): You are not allowed to create a user with GRANT', $e->getMessage());
        }

        $this->fail('CouldNotGrantPrivilegesException was not thrown.');
    }

    /** @test */
    public function it_can_create_a_database()
    {
        TaskRunner::fake(CreateDatabase::class);

        $this->manager->createDatabase('protone');

        TaskRunner::assertDispatched(CreateDatabase::class, function (PendingTask $task) {
            $this->assertEquals('CREATE DATABASE `protone` CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`;', $task->task->sql());

            return true;
        });
    }

    /** @test */
    public function it_throws_an_exception_when_it_cant_create_the_database()
    {
        TaskRunner::fake([
            CreateDatabase::class => ProcessOutput::make('ERROR 1007 (HY000): Can\'t create database \'protone\'; database exists')->setExitCode(0),
        ]);

        try {
            $this->manager->createDatabase('protone');
        } catch (DatabaseAlreadyExistsException $e) {
            return $this->assertEquals('ERROR 1007 (HY000): Can\'t create database \'protone\'; database exists', $e->getMessage());
        }

        $this->fail('DatabaseAlreadyExistsException was not thrown.');
    }

    /** @test */
    public function it_can_drop_a_database()
    {
        TaskRunner::fake(DropDatabase::class);

        $this->manager->dropDatabase('protone');

        TaskRunner::assertDispatched(DropDatabase::class, function (PendingTask $task) {
            $this->assertEquals('DROP DATABASE IF EXISTS `protone`;', $task->task->sql());

            return true;
        });
    }

    /** @test */
    public function it_throws_an_exception_when_it_cant_drop_the_database()
    {
        TaskRunner::fake([
            DropDatabase::class => ProcessOutput::make('ERROR 1008 (HY000): Can\'t drop database \'protone\'; database doesn\'t exist')->setExitCode(0),
        ]);

        try {
            $this->manager->dropDatabase('protone');
        } catch (DatabaseNotFoundException $e) {
            return $this->assertEquals('ERROR 1008 (HY000): Can\'t drop database \'protone\'; database doesn\'t exist', $e->getMessage());
        }

        $this->fail('DatabaseNotFoundException was not thrown.');
    }

    /** @test */
    public function it_can_drop_a_user_from_a_host()
    {
        TaskRunner::fake(DropUser::class);

        $this->manager->dropUser('protone');

        TaskRunner::assertDispatched(DropUser::class, function (PendingTask $task) {
            $this->assertEquals('DROP USER IF EXISTS `protone`@`1.2.3.4`; DROP USER IF EXISTS `protone`@`%`;', $task->task->sql());

            return true;
        });
    }

    /** @test */
    public function it_throws_an_exception_when_it_cant_drop_the_user()
    {
        TaskRunner::fake([
            DropUser::class => ProcessOutput::make('ERROR 1396 (HY000): Operation DROP USER failed for \'protone\'@\'%\'')->setExitCode(0),
        ]);

        try {
            $this->manager->dropUser('protone');
        } catch (CouldNotDropUserException $e) {
            return $this->assertEquals('ERROR 1396 (HY000): Operation DROP USER failed for \'protone\'@\'%\'', $e->getMessage());
        }

        $this->fail('CouldNotDropUserException was not thrown.');
    }
}
