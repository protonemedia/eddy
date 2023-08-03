<?php

namespace App\Tasks;

use App\Models\Task as TaskModel;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelTaskRunner\Task as BaseTask;

abstract class Task extends BaseTask
{
    use SerializesModels;
    use HandlesCallbacks;

    protected ?TaskModel $taskModel = null;

    public function setTaskModel(TaskModel $taskModel): self
    {
        $this->taskModel = $taskModel;

        return $this;
    }

    public function onOutputUpdated(string $output): void
    {
    }

    public function callbackUrl(): ?string
    {
        return $this instanceof HasCallbacks ? $this->taskModel?->callbackUrl() : null;
    }

    public function getScript(): string
    {
        $formatter = app(Formatter::class);

        return $formatter->bash(parent::getScript());
    }
}
