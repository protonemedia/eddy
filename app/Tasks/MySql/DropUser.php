<?php

namespace App\Tasks\MySql;

class DropUser extends MySqlTask
{
    public function __construct(public string $user)
    {
    }

    /**
     * The SQL query to run.
     */
    public function sql(): string
    {
        return $this->withHosts(fn ($host) => sprintf(
            'DROP USER IF EXISTS %s@%s;',
            static::wrapValue($this->user),
            static::wrapValue($host),
        ));
    }
}
