<?php

namespace App\Tasks;

class RestartSupervisor extends RestartService
{
    protected string $service = 'supervisor';
}
