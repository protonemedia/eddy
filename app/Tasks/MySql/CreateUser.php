<?php

namespace App\Tasks\MySql;

class CreateUser extends MySqlTask
{
    public function __construct(public string $name, public string $password)
    {
    }

    /**
     * The SQL query to run.
     */
    public function sql(): string
    {
        return $this->withHosts(fn ($host) => sprintf(
            "CREATE USER IF NOT EXISTS %s@%s IDENTIFIED BY \"{$this->password}\";",
            static::wrapValue($this->name),
            static::wrapValue($host),
        ));
    }
}
