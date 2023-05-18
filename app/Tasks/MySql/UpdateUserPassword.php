<?php

namespace App\Tasks\MySql;

class UpdateUserPassword extends MySqlTask
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
            "ALTER USER %s@%s IDENTIFIED BY \"{$this->password}\";",
            static::wrapValue($this->name),
            static::wrapValue($host),
        )).' FLUSH PRIVILEGES;';
    }
}
