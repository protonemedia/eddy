<?php

namespace App\Jobs;

use App\Models\Cron;
use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InstallWordpressCron implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Site $site)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $server = $this->site->server;

        /** @var Cron */
        $cron = $server->crons()->create([
            'command' => $this->site->php_version->getBinary().' '.$this->site->getWebDirectory().'/wp-cron.php',
            'user' => $this->site->user,
            'expression' => '* * * * *',
        ]);

        dispatch(new InstallCron($cron));
    }
}
