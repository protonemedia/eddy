<?php

namespace App\Jobs;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InstallTaskCleanupCron implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Server $server)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $workingDirectory = $this->server->working_directory;

        $command = implode(' ; ', [
            "find /root/{$workingDirectory} -name \"task-*\" -type f -mtime +7 -exec rm {} \;",
            "find /home/eddy/{$workingDirectory} -name \"task-*\" -type f -mtime +7 -exec rm {} \;",
        ]);

        $this->server->uploadAsRoot(
            '/etc/cron.d/eddy-tasks-cleanup',
            view('tasks.eddy-tasks-cleanup', ['command' => $command])->render()
        );
    }
}
