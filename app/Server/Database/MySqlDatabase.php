<?php

namespace App\Server\Database;

use App\Models\Server;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MySqlDatabase implements DatabaseManager
{
    public function __construct(public Server $server)
    {
    }

    /**
     * List of databases that should not be messed with.
     */
    public static function protectedDatabases(): array
    {
        return [
            'information_schema',
            'mysql',
            'performance_schema',
            'sys',
        ];
    }

    /**
     * Run the task on the server and return the output.
     */
    private function run(string|MySqlTask $task): string
    {
        /** @var MySqlTask */
        $task = is_string($task) ? new $task($this->server) : $task;
        $task->onServer($this->server);

        $output = $this->server->runTask($task)->asUser()->dispatch();

        if (! $output->isSuccessful()) {
            throw new CouldNotConnectToDatabaseException($this->server, 'Could not connect to the database.');
        }

        $buffer = $output->getBuffer();

        if (Str::containsAll($buffer, ['ERROR 2002 (HY000)', "Can't connect to local MySQL server"])) {
            throw new CouldNotConnectToDatabaseException($this->server, $buffer);
        }

        if (Str::containsAll($buffer, ['ERROR 1045 (28000)', 'Access denied for user'])) {
            throw new CouldNotAuthenticateWithDatabaseException($this->server, $buffer);
        }

        return $buffer;
    }

    /**
     * Parse the lines from the output.
     */
    private static function parseLines(string $lines): Collection
    {
        return Collection::make(explode(PHP_EOL, $lines));
    }

    /**
     * Get all databases from the database.
     *
     * @return array<string>
     */
    public function getDatabases(): array
    {
        $databases = $this->run(GetDatabases::class);

        return self::parseLines($databases)->filter()->reject(function (string $database) {
            return in_array($database, static::protectedDatabases());
        })->unique()->filter()->sort()->values()->all();
    }

    /**
     * Get all users from the database.
     *
     * @return array<\App\Server\Database\UserHost>
     */
    public function getUsers(): array
    {
        $users = $this->run(GetUsers::class);

        return self::parseLines($users)->filter()->map(function ($lineWithUserAndHost) {
            $segments = explode("\t", $lineWithUserAndHost);

            return new UserHost(user: $segments[1], host: $segments[0]);
        })->reject(function (UserHost $userHost) {
            return Str::startsWith($userHost->user, 'mysql.');
        })->sortBy('user')->filter()->values()->all();
    }

    /**
     * Get all tables from the database.
     *
     * @return array<string>
     */
    public function getTables(string $database): array
    {
        $tables = $this->run(new GetTables($database));

        return self::parseLines($tables)->filter()->unique()->values()->all();
    }

    /**
     * Create a new user on the given host.
     */
    public function createUser(string $name, string $password): bool
    {
        $output = $this->run(new CreateUser($name, $password));

        if (Str::containsAll($output, ['ERROR 1396 (HY000)', 'Operation CREATE USER failed'])) {
            throw new CouldNotCreateUserException($this->server, $output);
        }

        return true;
    }

    /**
     * Update the password of the given user.
     */
    public function updateUserPassword(string $name, string $password): bool
    {
        $output = $this->run(new UpdateUserPassword($name, $password));

        if (Str::containsAll($output, ['ERROR 1396 (HY000)', 'Operation CREATE USER failed'])) {
            throw new CouldNotCreateUserException($this->server, $output);
        }

        return true;
    }

    /**
     * Grant all privileges to the given user on the given database and host.
     */
    public function grantAllPrivileges(string $user, string $database): bool
    {
        $output = $this->run(new GrantAllPrivileges($user, $database));

        if (Str::containsAll($output, ['ERROR 1410'])) {
            throw new CouldNotGrantPrivilegesException($output);
        }

        return true;
    }

    /**
     * Revoke all privileges to the given user on the given database and host.
     */
    public function revokeAllPrivileges(string $user, string $database): bool
    {
        $output = $this->run(new RevokeAllPrivileges($user, $database));

        if (Str::containsAll($output, ['ERROR 1410'])) {
            throw new CouldNotRevokePrivilegesException($output);
        }

        return true;
    }

    /**
     * Create a new database.
     */
    public function createDatabase(string $name, string $charset = 'utf8mb4', string $collation = 'utf8mb4_unicode_ci'): bool
    {
        $output = $this->run(new CreateDatabase($name, $charset, $collation));

        if (Str::containsAll($output, ['ERROR 1007 (HY000)', 'database exists'])) {
            throw new DatabaseAlreadyExistsException($this->server, $output);
        }

        return true;
    }

    /**
     * Drop a database.
     */
    public function dropDatabase(string $name): bool
    {
        $output = $this->run(new DropDatabase($name));

        if (Str::containsAll($output, ['ERROR 1008 (HY000)', 'database doesn\'t exist'])) {
            throw new DatabaseNotFoundException($this->server, $output);
        }

        return true;
    }

    /**
     * Drop a user from the given host.
     */
    public function dropUser(string $user): bool
    {
        $output = $this->run(new DropUser($user));

        if (Str::containsAll($output, ['ERROR 1396 (HY000)', 'Operation DROP USER failed'])) {
            throw new CouldNotDropUserException($this->server, $output);
        }

        return true;
    }
}
