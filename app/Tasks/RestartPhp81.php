<?php

namespace App\Tasks;

class RestartPhp81 extends RestartService
{
    protected string $service = 'php8.1-fpm';
}
