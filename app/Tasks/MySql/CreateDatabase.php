<?php

namespace App\Tasks\MySql;

class CreateDatabase extends MySqlTask
{
    public function __construct(public string $name, public string $charset, public string $collation)
    {
    }

    /**
     * The SQL query to run.
     */
    public function sql(): string
    {
        return sprintf(
            'CREATE DATABASE %s CHARACTER SET %s COLLATE %s;',
            static::wrapValue($this->name),
            static::wrapValue($this->charset),
            static::wrapValue($this->collation),
        );
    }
}
