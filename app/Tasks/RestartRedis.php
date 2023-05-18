<?php

namespace App\Tasks;

class RestartRedis extends RestartService
{
    protected string $service = 'redis-server';
}
