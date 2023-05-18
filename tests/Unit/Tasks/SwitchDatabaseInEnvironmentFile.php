<?php

namespace Tests\Unit\Tasks;

use Illuminate\Filesystem\Filesystem;

trait SwitchDatabaseInEnvironmentFile
{
    public function setUpSwitchDatabaseInEnvironmentFile()
    {
        $databaseConnection = config('database.default');

        $filesystem = new Filesystem;

        $filesystem->copy(
            base_path('.env'),
            base_path('.env.backup')
        );

        $env = preg_replace(
            '^(DB_CONNECTION=)(.)*^',
            "DB_CONNECTION={$databaseConnection}",
            file_get_contents(base_path('.env'))
        );

        file_put_contents(base_path('.env'), $env);

        $this->beforeApplicationDestroyed(fn () => $this->restoreEnvironmentFile());

        register_shutdown_function(fn () => $this->restoreEnvironmentFile());
    }

    public function restoreEnvironmentFile()
    {
        $filesystem = new Filesystem;

        if (! $filesystem->exists(base_path('.env.backup'))) {
            return;
        }

        $filesystem->copy(
            base_path('.env.backup'),
            base_path('.env')
        );

        $filesystem->delete(base_path('.env.backup'));
    }
}
