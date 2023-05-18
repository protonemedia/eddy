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

class UpdateDatabaseUser implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public DatabaseUser $databaseUser,
        public ?string $password = null,
        public ?User $user = null
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $manager = $this->databaseUser->server->databaseManager();

        if ($this->password) {
            $manager->updateUserPassword($this->databaseUser->name, $this->password);
        }

        $this->databaseUser->server->databases->each(function (Database $database) use ($manager) {
            if ($this->databaseUser->databases->contains($database)) {
                $manager->grantAllPrivileges($this->databaseUser->name, $database->name);
            } else {
                $manager->revokeAllPrivileges($this->databaseUser->name, $database->name);
            }
        });

        $this->databaseUser->forceFill([
            'installed_at' => $this->databaseUser->installed_at ?: now(),
        ])->save();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->databaseUser->forceFill([
            'installation_failed_at' => now(),
        ])->save();

        $this->databaseUser->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__("Updating the database user ':name'", ['name' => "`{$this->databaseUser->name}`"]))
            ->send();
    }
}
