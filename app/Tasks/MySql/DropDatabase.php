<?php

namespace App\Tasks\MySql;

class DropDatabase extends MySqlTask
{
    public function __construct(public string $name)
    {
    }

    /**
     * The SQL query to run.
     */
    public function sql(): string
    {
        return sprintf(
            'DROP DATABASE IF EXISTS %s;',
            static::wrapValue($this->name),
        );
    }
}
