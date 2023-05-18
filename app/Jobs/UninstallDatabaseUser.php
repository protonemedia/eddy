<?php

namespace App\Jobs;

use App\Models\DatabaseUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UninstallDatabaseUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public DatabaseUser $databaseUser, public ?User $user = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->databaseUser->server->databaseManager()->dropUser($this->databaseUser->name);
        $this->databaseUser->delete();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->databaseUser->forceFill(['uninstallation_failed_at' => now()])->save();

        $this->databaseUser->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__("Uninstallation of database user ':name'", ['name' => "`{$this->databaseUser->name}`"]))
            ->send();
    }
}
