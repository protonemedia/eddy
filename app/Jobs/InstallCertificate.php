<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Models\TlsSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class InstallCertificate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Certificate $certificate, public ?User $user = null)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $site = $this->certificate->site;

        $site->uploadAsUser($this->certificate->certificatePath(), $this->certificate->certificate, throw: true);
        $site->uploadAsUser($this->certificate->privateKeyPath(), $this->certificate->private_key, throw: true);

        // Get rid of the certificate and private key in database
        $this->certificate->forceFill([
            'private_key' => null,
            'certificate' => null,
            'uploaded_at' => now(),
        ])->save();

        dispatch(new UpdateSiteTlsSetting($site, TlsSetting::Custom, $this->certificate));
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->certificate->site->forceFill([
            'pending_tls_update_since' => null,
        ])->save();

        $this->certificate->delete();

        $this->certificate->site->server
            ->exceptionHandler()
            ->notify($this->user)
            ->about($exception)
            ->withReference(__('Install a custom SSL certificate for :site', ['site' => $this->certificate->site->address]))
            ->send();
    }
}
