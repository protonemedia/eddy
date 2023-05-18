<?php

namespace App\Tasks;

class RestartPhp82 extends RestartService
{
    protected string $service = 'php8.2-fpm';
}
