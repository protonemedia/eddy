<?php

namespace App\Tasks\MySql;

class GetDatabases extends MySqlTask
{
    /**
     * The SQL query to run.
     */
    public function sql(): string
    {
        return 'SHOW DATABASES;';
    }
}
