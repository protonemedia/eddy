<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Provider;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class CleanupVagrantServers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-vagrant-servers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Destroy and cleanup all local Vagrant servers.';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $filesystem): void
    {
        Collection::make($filesystem->directories(config('services.vagrant.path')))->each(function ($serverPath) use ($filesystem) {
            $serverId = Str::afterLast($serverPath, '/');

            if (! is_numeric($serverId)) {
                return;
            }

            $this->info("Vagrant destroy {$serverId}");

            Process::path($serverPath)->run('vagrant destroy -f');

            $this->info('Deleting Server Model');

            Server::query()->where('provider', Provider::Vagrant)->where('provider_id', $serverId)->first()?->deleteQuietly();

            $this->info('Deleting Server Directory');

            $filesystem->deleteDirectory($serverPath);
        });
    }
}
