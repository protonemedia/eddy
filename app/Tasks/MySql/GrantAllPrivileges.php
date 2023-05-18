<?php

namespace App\Tasks\MySql;

class GrantAllPrivileges extends MySqlTask
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
            'GRANT ALL ON %s.* TO %s@%s WITH GRANT OPTION;',
            static::wrapValue($this->database),
            static::wrapValue($this->name),
            static::wrapValue($host),
        )).' FLUSH PRIVILEGES;';
    }
}
