<?php

namespace App\Tasks;

use Illuminate\Support\Str;

class ValidateMySqlConfig extends Task
{
    public string $path;

    protected string $view = 'validate-mysql-config';

    public function __construct(public string $mysqlConfig)
    {
        $id = Str::random();

        $this->path = "/tmp/mysql-{$id}.cnf";
    }
}
