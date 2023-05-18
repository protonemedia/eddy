<?php

namespace Tests\Unit\Jobs;

use App\Jobs\InstallCertificate;
use App\Jobs\UpdateSiteTlsSetting;
use App\Models\Certificate;
use App\Models\TlsSetting;
use App\Notifications\JobOnServerFailed;
use App\Tasks\UploadFile;
use Database\Factories\CertificateFactory;
use Database\Factories\SiteFactory;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use ProtoneMedia\LaravelTaskRunner\Facades\TaskRunner;
use Tests\TestCase;

class InstallCertificateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uploads_the_files_and_dispatches_a_job_to_update_the_caddyfile()
    {
        TaskRunner::fake();
        Bus::fake();

        $site = SiteFactory::new()->installed()->create([
            'address' => 'example.com',
        ]);

        /** @var Certificate */
        $certificate = CertificateFactory::new()->create(['site_id' => $site->id]);
        $certificate->id = 1;

        $duplicate = $certificate->replicate();

        $this->assertEquals('/home/eddy/example.com/certificates/1/certificate.cert', $certificate->certificatePath());
        $this->assertEquals('/home/eddy/example.com/certificates/1/private.key', $certificate->privateKeyPath());

        //

        $job = new InstallCertificate($certificate);
        $job->handle();

        $this->assertNull($certificate->fresh()->private_key);
        $this->assertNull($certificate->fresh()->certificate);
        $this->assertNotNull($certificate->fresh()->uploaded_at);

        TaskRunner::assertDispatchedTimes(UploadFile::class, 2);

        TaskRunner::assertDispatched(function (UploadFile $task) use ($certificate, $duplicate) {
            return $task->path === $certificate->certificatePath()
                && $task->contents === $duplicate->certificate;
        });

        TaskRunner::assertDispatched(function (UploadFile $task) use ($certificate, $duplicate) {
            return $task->path === $certificate->privateKeyPath()
                && $task->contents === $duplicate->private_key;
        });

        Bus::assertDispatched(function (UpdateSiteTlsSetting $job) use ($certificate) {
            $this->assertTrue($job->site->is($certificate->site));
            $this->assertEquals(TlsSetting::Custom, $job->tlsSetting);
            $this->assertTrue($job->certificate->is($certificate));

            return true;
        });
    }

    /** @test */
    public function it_handles_job_failure()
    {
        Notification::fake();

        $site = SiteFactory::new()->create(['pending_tls_update_since' => now()]);
        $certificate = CertificateFactory::new()->forSite($site)->create();
        $user = UserFactory::new()->create();

        $job = new InstallCertificate($certificate, $user);
        $job->failed(new Exception('Installation failed.'));

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'pending_tls_update_since' => null,
        ]);

        Notification::assertSentTo($user, function (JobOnServerFailed $notification) use ($site) {
            return $notification->server->is($site->server);
        });
    }
}
