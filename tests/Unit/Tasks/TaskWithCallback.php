<?php

namespace Tests\Unit\Tasks;

use App\Models\Task as TaskModel;
use App\Tasks\HasCallbacks;
use App\Tasks\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class TaskWithCallback extends Task implements HasCallbacks
{
    public function onCustomCallback(TaskModel $task, Request $request)
    {
        if ($request->input('foo') === 'bar') {
            $task->update(['name' => 'Hi from callback!']);
        }
    }

    public function render()
    {
        return Blade::render('
echo "Start"
<x-task-callback :url="$callbackUrl()" :data="[\'foo\' => \'bar\']" />
            ', $this->getData());
    }
}
