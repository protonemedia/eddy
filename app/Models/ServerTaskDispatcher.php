<?php

namespace App\Models;

use App\Jobs\UpdateTaskOutput;
use App\Models\Task as TaskModel;
use App\Tasks\HasCallbacks;
use App\Tasks\Task;
use App\Tasks\TrackTaskInBackground;
use Illuminate\Support\Traits\Conditionable;
use ProtoneMedia\LaravelTaskRunner\CouldNotCreateScriptDirectoryException;
use ProtoneMedia\LaravelTaskRunner\CouldNotUploadFileException;
use ProtoneMedia\LaravelTaskRunner\PendingTask;
use ProtoneMedia\LaravelTaskRunner\ProcessOutput;

class ServerTaskDispatcher
{
    use Conditionable;

    /**
     * A flag to indicate if the task should be tracked in the background.
     */
    private bool $keepTrack = false;

    /**
     * A flag to indicate if the task should throw an exception if it fails.
     */
    private bool $throwException = false;

    /**
     * The interval in seconds to update the task log.
     */
    private int $updateLogIntervalInSeconds = 0;

    public function __construct(
        private Server $server,
        private PendingTask $pendingTask,
    ) {
    }

    /**
     * Enables the tracking of the task in the background.
     */
    public function keepTrackInBackground(bool $value = true): self
    {
        if ($value) {
            $this->inBackground(true);
        }

        $this->keepTrack = $value;

        return $this;
    }

    /**
     * Run the task as the root user.
     */
    public function asRoot(): self
    {
        $this->pendingTask->onConnection($this->server->connectionAsRoot());

        return $this;
    }

    /**
     * Run the task as the non-root user.
     */
    public function asUser(string $username = null): self
    {
        $this->pendingTask->onConnection($this->server->connectionAsUser($username));

        return $this;
    }

    /**
     * Sets the task to run in the background.
     */
    public function inBackground(bool $value = true): self
    {
        $this->pendingTask->inBackground($value);

        return $this;
    }

    /**
     * Sets the interval in seconds to update the task log.
     */
    public function updateLogIntervalInSeconds(int $value): self
    {
        $this->inBackground();

        $this->updateLogIntervalInSeconds = $value;

        return $this;
    }

    /**
     * Sets a custom name for the task.
     */
    public function as(string $id): self
    {
        $this->pendingTask->as($id);

        return $this;
    }

    /**
     * Creates a Task Model and sets the name of the pending task to the id of the model.
     */
    private function createTask(): TaskModel
    {
        $actualTask = $this->pendingTask->task;

        return $this->server->tasks()->create([
            'name' => $actualTask->getName(),
            'user' => $this->pendingTask->getConnection()->is($this->server->connectionAsRoot()) ? 'root' : $this->server->username,
            'type' => get_class($actualTask),
            'script' => $actualTask->getScript(),
            'timeout' => $actualTask->getTimeout() ?: 0,
            'status' => TaskStatus::Pending,
            'instance' => $actualTask instanceof HasCallbacks ? serialize($actualTask) : null,
        ]);
    }

    /**
     * Replaces the pending task with a new one that has the actual task as its child.
     */
    private function dispatchAndKeepTrack(): TaskModel
    {
        // Create the Eloquent model for the task
        $taskModel = $this->createTask();

        /** @var Task */
        $actualTask = $this->pendingTask->task;

        // Create a new task that will track the actual task
        $taskWithTracking = new TrackTaskInBackground(
            $actualTask,
            $taskModel->finishedUrl(),
            $taskModel->failedUrl(),
            $taskModel->timeoutUrl(),
        );

        if ($actualTask instanceof HasCallbacks) {
            $actualTask->setTaskModel($taskModel);

            // Rerender the script to include the task callbacks
            $taskModel->update(['script' => $actualTask->getScript()]);
        }

        // Replace pending task with a new one that has the tracking task as its parent
        $this->pendingTask = PendingTask::make($taskWithTracking)
            ->as('task-'.$taskModel->id)
            ->onConnection($this->pendingTask->getConnection())
            ->inBackground();

        try {
            $this->dispatchPendingTask();
        } catch (CouldNotConnectToServerException $e) {
            return tap($taskModel)->update([
                'status' => TaskStatus::UploadFailed,
            ]);
        }

        // Dispatch the job to update the task log
        if ($this->updateLogIntervalInSeconds > 0) {
            UpdateTaskOutput::dispatch($taskModel, $this->updateLogIntervalInSeconds)->delay(5);
        }

        return tap($taskModel)->update([
            'status' => TaskStatus::Pending,
        ]);
    }

    /**
     * Dispatches the pending task.
     */
    private function dispatchPendingTask(): ProcessOutput|null
    {
        try {
            $processOutput = $this->pendingTask->dispatch();
        } catch (CouldNotCreateScriptDirectoryException|CouldNotUploadFileException $e) {
            throw new CouldNotConnectToServerException(
                $this->server,
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return $processOutput;
    }

    /**
     * Throws an exception if the task fails.
     */
    public function throw(bool $value = true): self
    {
        $this->throwException = $value;

        return $this;
    }

    /**
     * Dispatches the task.
     */
    public function dispatch(): ProcessOutput|TaskModel|null
    {
        if ($this->pendingTask->getConnection() == null) {
            throw new NoConnectionSelectedException;
        }

        if ($this->keepTrack) {
            return $this->dispatchAndKeepTrack();
        }

        if (! $this->pendingTask->getId()) {
            $this->pendingTask->id((new TaskModel)->newUniqueId());
        }

        return tap($this->dispatchPendingTask(), function (ProcessOutput $output) {
            if (! $output->isSuccessful() && $this->throwException) {
                throw new TaskFailedException(
                    "The task `{$this->pendingTask->task->getName()}` failed with the following output: {$output->getBuffer()}"
                );
            }
        });
    }
}
