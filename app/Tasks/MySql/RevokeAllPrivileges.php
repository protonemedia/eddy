<?php

namespace App\Tasks\MySql;

class RevokeAllPrivileges extends MySqlTask
{
    public function __construct(public string $name, public string $database)
    {
    }

     /**
      * The SQL query to run.
      */
     public function sql(): string
     {
         return $this->withHosts(fn ($host) => sprintf(
             'REVOKE ALL ON %s.* FROM %s@%s;',
             static::wrapValue($this->database),
             static::wrapValue($this->name),
             static::wrapValue($host),
         )).' FLUSH PRIVILEGES;';
     }
}
