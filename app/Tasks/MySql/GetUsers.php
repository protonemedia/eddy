<?php

namespace App\Tasks\MySql;

class GetUsers extends MySqlTask
{
    /**
     * The SQL query to run.
     */
    public function sql(): string
    {
        return 'SELECT host, user FROM mysql.user;';
    }
}
