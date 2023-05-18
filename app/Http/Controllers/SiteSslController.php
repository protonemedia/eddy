<?php

namespace App\Http\Controllers;

use App\Enum;
use App\Jobs\InstallCertificate;
use App\Jobs\UpdateSiteTlsSetting;
use App\Models\Server;
use App\Models\Site;
use App\Models\TlsSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use ProtoneMedia\Splade\Facades\Toast;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class SiteSslController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server, Site $site)
    {
        if ($site->pending_tls_update_since?->diffInMinutes() > 3) {
            // Clear the pending TLS update after 3 minutes.
            $site->forceFill(['pending_tls_update_since' => null])->saveQuietly();
        }

        return view('sites.ssl.edit', [
            'server' => $server,
            'site' => $site,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server, Site $site)
    {
        $customFieldsRequiredIf = Rule::requiredIf(function () use ($request, $site) {
            return $request->input('tls_setting') === TlsSetting::Custom->value && ! $site->activeCertificate;
        });

        $data = $request->validate([
            'tls_setting' => ['required', Enum::rule(TlsSetting::class)],
            'private_key' => ['nullable', $customFieldsRequiredIf, 'required_with:certificate', 'string'],
            'certificate' => ['nullable', $customFieldsRequiredIf, 'required_with:private_key', 'string'],
        ]);

        $newCertificate = null;

        if ($data['private_key']) {
            $newCertificate = $site->certificates()->create([
                'private_key' => $data['private_key'],
                'certificate' => $data['certificate'],
            ]);
        }

        $newTlsSetting = TlsSetting::from($data['tls_setting']);

        $site->pending_tls_update_since = now();

        if ($newCertificate) {
            dispatch(new InstallCertificate($newCertificate, $this->user()));

            Toast::info(__('The certificate will be uploaded to the server and installed. This may take a few minutes.'));
        } elseif ($site->tls_setting !== $newTlsSetting) {
            dispatch(new UpdateSiteTlsSetting($site, $newTlsSetting, $newCertificate, $this->user()));

            Toast::info(__('The site SSL settings will be updated. It might take a few minutes before the changes are applied.'));
        } else {
            Toast::warning(__('No changes were made.'));

            $site->pending_tls_update_since = null;
        }

        $site->saveQuietly();

        if ($site->pending_tls_update_since) {
            $this->logActivity(__("Updated SSL settings of site ':address' on server ':server'", ['address' => $site->address, 'server' => $server->name]), $site);
        }

        return to_route('servers.sites.ssl.edit', [$server, $site]);
    }
}
