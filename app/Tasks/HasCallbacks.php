<?php

namespace App\Tasks;

use App\Models\Task;
use Illuminate\Http\Request;

interface HasCallbacks
{
    public function handleCallback(Task $task, Request $request, CallbackType $type);
}
