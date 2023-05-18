<?php

namespace App\Jobs;

use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class InstallDatabaseUser implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public DatabaseUser $databaseUser, public string $password, public ?User $user = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $manager = $this->databaseUser->server->databaseManager();

        $manager->createUser($this->databaseUser->name, $this->password);

        $this->databaseUser->databases->each(function (Database $database) use ($manager) {
            $manager->grantAllPrivileges($this->databaseUser->name, $database->name);
        });

        $this->databaseUser->forceFill([
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
        // Clean up the database user if it was created.
        rescue(function () {
            $this->databaseUser->server->databaseManager()->dropUser($this->databaseUser->name);
        }, report: false);

        $this->databaseUser->forceFill(['installation_failed_at' => now()])->save();

        $this->databaseUser->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__("Creation of database user ':name'", ['name' => "`{$this->databaseUser->name}`"]))
            ->send();
    }
}
