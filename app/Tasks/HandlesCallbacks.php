<?php

namespace App\Tasks;

use App\Models\Task as TaskModel;
use Illuminate\Http\Request;

/**
 * @codeCoverageIgnore Handled by DeploySiteTest and TaskWithCallbackTest.
 */
trait HandlesCallbacks
{
    public function handleCallback(TaskModel $task, Request $request, CallbackType $callbackType)
    {
        return match ($callbackType) {
            CallbackType::Timeout => $this->onTimeout($task, $request),
            CallbackType::Failed => $this->onFailed($task, $request),
            CallbackType::Finished => $this->onFinished($task, $request),
            CallbackType::Custom => $this->onCustomCallback($task, $request),
        };
    }

    protected function onTimeout(TaskModel $task, Request $request)
    {
    }

    protected function onFailed(TaskModel $task, Request $request)
    {
    }

    protected function onFinished(TaskModel $task, Request $request)
    {
    }

    protected function onCustomCallback(TaskModel $task, Request $request)
    {
    }
}
