<?php

namespace Tests\Feature;

use App\Models\Task as TaskModel;
use App\Tasks\HasCallbacks;
use App\Tasks\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TaskWithCallbacks extends Task implements HasCallbacks
{
    protected function onTimeout(TaskModel $task, Request $request)
    {
        Cache::driver('file')->put('test-task', 'timeout');
    }

    protected function onFailed(TaskModel $task, Request $request)
    {
        Cache::driver('file')->put('test-task', 'failed');
    }

    protected function onFinished(TaskModel $task, Request $request)
    {
        Cache::driver('file')->put('test-task', 'finished');
    }

    protected function onCustomCallback(TaskModel $task, Request $request)
    {
        Cache::driver('file')->put('test-task', $request->only('foo'));
    }
}
