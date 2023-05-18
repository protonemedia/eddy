<?php

namespace App\Jobs;

use App\CaddyfilePatcher;
use App\Models\Site;
use App\Models\User;
use App\Server\PhpVersion;
use App\Tasks\GetFile;
use App\Tasks\PrettifyCaddyfile;
use App\Tasks\UpdateCaddyfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UpdateSiteCaddyfile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Site $site,
        public PhpVersion $phpVersion,
        public string $webFolder,
        public ?User $user = null,
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = $this->site->files()->caddyfile()->path;

        // Prettify the Caddyfile before we start.
        $this->site->runTaskAsUser(new PrettifyCaddyfile($path))->throw()->dispatch();

        // Get the current Caddyfile.
        $currentCaddyfile = $this->site->runTaskAsUser(new GetFile($path))->throw()->dispatch()->getBuffer();

        // Patch the Caddyfile.

        /** @var CaddyfilePatcher */
        $patcher = app()->makeWith(CaddyfilePatcher::class, ['site' => $this->site, 'caddyfile' => $currentCaddyfile]);
        $newCaddyfile = $patcher->replacePhpVersion($this->phpVersion)
            ->replacePublicFolder($this->site->generateWebDirectory($this->webFolder))
            ->get();

        // Update the Caddyfile on the server.
        $this->site->runTaskAsUser(new UpdateCaddyfile($this->site, $newCaddyfile))->throw()->dispatch();

        $this->site->forceFill([
            'pending_caddyfile_update_since' => null,
            'php_version' => $this->phpVersion,
            'web_folder' => $this->webFolder,
        ])->save();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->site->forceFill([
            'pending_caddyfile_update_since' => null,
        ])->save();

        $this->site->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__('Update the Caddyfile for :site', ['site' => $this->site->address]))
            ->send();
    }
}
