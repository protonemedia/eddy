<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateTaskOutput implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 30;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Task $task, public int $dispatchNewJobAfter = 0)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->task->updateOutput();

        if ($this->task->isOlderThanTimeout()) {
            $this->task->update([
                'status' => TaskStatus::Timeout,
                'exit_code' => 124,
            ]);
        }

        if ($this->dispatchNewJobAfter > 0 && $this->task->status === TaskStatus::Pending) {
            static::dispatch($this->task, $this->dispatchNewJobAfter)->delay($this->dispatchNewJobAfter);
        }
    }
}
