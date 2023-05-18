<?php

namespace App\Tasks\MySql;

class GetTables extends MySqlTask
{
    public function __construct(protected string $database)
    {
    }

    /**
     * The SQL query to run.
     */
    public function sql(): string
    {
        return "USE {$this->database}; SHOW TABLES;";
    }
}
