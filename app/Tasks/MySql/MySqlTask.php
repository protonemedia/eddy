<?php

namespace App\Tasks\MySql;

use App\Models\Server;
use App\Tasks\Task;
use Exception;
use Illuminate\Support\Collection;

abstract class MySqlTask extends Task
{
    protected array $hosts = ['%'];

    protected ?string $mySqlUser = null;

    protected ?string $mySqlPassword = null;

    public function getHosts(): array
    {
        return $this->hosts;
    }

    public function withHosts(callable $callback): string
    {
        return Collection::make($this->hosts)->map($callback)->implode(' ');
    }

    /**
     * Set the credentials to use for the MySQL connection.
     */
    public function withCredentials(string $user, string $password): self
    {
        $this->mySqlUser = $user;
        $this->mySqlPassword = $password;

        return $this;
    }

    /**
     * Set the credentials to the server's database credentials.
     */
    public function onServer(Server $server): self
    {
        $this->hosts = [$server->public_ipv4, '%'];

        return $this->withCredentials('root', $server->database_password);
    }

    /**
     * The SQL query to run.
     */
    abstract public function sql(): string;

    /**
     * Prepare a value for use in a SQL query.
     *
     * @see MySqlGrammar::wrapValue()
     */
    public static function wrapValue(string $value): string
    {
        if ($value === '*') {
            return $value;
        }

        // Replace single quotes with backticks
        $value = str_replace('\'', '`', $value);

        // Replace backticks with double backticks and wrap in backticks
        return '`'.str_replace('`', '``', $value).'`';
    }

    /**
     * The SQL query to run, wrapped in a command with the credentials.
     */
    public function render(): string
    {
        if (! $this->mySqlUser || ! $this->mySqlPassword) {
            throw new Exception('Forgot to set the user or password.');
        }

        return "MYSQL_PWD={$this->mySqlPassword} mysql --user={$this->mySqlUser} --execute='{$this->sql()}' --skip-column-names";
    }
}
