<?php

namespace App\Jobs;

use App\Models\Database;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class InstallDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Database $database, public ?User $user = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->database
            ->server
            ->databaseManager()
            ->createDatabase($this->database->name);

        $this->database->forceFill([
            'installed_at' => now(),
            'installation_failed_at' => null,
        ])->save();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        // Clean up the database if it was created.
        rescue(function () {
            $this->database->server->databaseManager()->dropDatabase($this->database->name);
        }, report: false);

        $this->database->forceFill(['installation_failed_at' => now()])->save();

        $this->database->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__("Creation of database ':database'", ['database' => "`{$this->database->name}`"]))
            ->send();
    }
}
