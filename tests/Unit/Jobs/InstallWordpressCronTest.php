<?php

namespace Tests\Unit\Jobs;

use App\Jobs\InstallCron;
use App\Jobs\InstallWordpressCron;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class InstallWordpressCronTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_installs_a_wordpress_cron_for_the_site()
    {
        $site = SiteFactory::new()->wordpressApp()->create([
            'address' => 'wordpress.com',
        ]);

        Bus::fake();

        $job = new InstallWordpressCron($site);
        $job->handle();

        $cron = $site->server->crons->first();

        $this->assertEquals('eddy', $cron->user);
        $this->assertEquals('/usr/bin/php8.1 /home/eddy/wordpress.com/current/wp-cron.php', $cron->command);
        $this->assertEquals('* * * * *', $cron->expression);

        Bus::assertDispatched(InstallCron::class, function (InstallCron $job) use ($cron) {
            return $job->cron->is($cron);
        });
    }
}
