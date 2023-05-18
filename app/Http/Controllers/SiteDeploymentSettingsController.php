<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\RequiredIf;
use ProtoneMedia\Splade\Facades\Toast;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class SiteDeploymentSettingsController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server, Site $site)
    {
        return view('sites.deployments.settings', [
            'server' => $server,
            'site' => $site,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server, Site $site)
    {
        $data = $request->validate([
            'deploy_notification_email' => ['nullable', 'email'],
            'shared_directories' => ['nullable', 'string'],
            'shared_files' => ['nullable', 'string'],
            'writeable_directories' => ['nullable', 'string'],
            'hook_before_updating_repository' => ['nullable', 'string'],
            'hook_after_updating_repository' => ['nullable', 'string'],
            'hook_before_making_current' => ['nullable', 'string'],
            'hook_after_making_current' => ['nullable', 'string'],
            'deployment_releases_retention' => ['nullable', new RequiredIf($site->zero_downtime_deployment), 'integer', 'min:1', 'max:50'],
        ]);

        foreach (['hook_before_updating_repository', 'hook_after_updating_repository', 'hook_before_making_current', 'hook_after_making_current'] as $hook) {
            // Replace new lines with new lines that are compatible with bash
            $data[$hook] = str_replace(["\r\n", "\n", "\r"], "\n", $data[$hook] ?? '');
        }

        foreach (['shared_directories', 'shared_files', 'writeable_directories'] as $key) {
            $data[$key] = collect(explode(PHP_EOL, $data[$key] ?? ''))
                ->map(fn ($item) => trim($item))
                ->filter(fn ($item) => $item !== '')
                ->values()
                ->all();
        }

        $site->update($data);

        $this->logActivity(__("Updated deployment settings of site ':address' on server ':server'", ['address' => $site->address, 'server' => $server->name]), $site);

        Toast::message(__('The deployment settings have been saved.'));

        return to_route('servers.sites.deployment-settings.edit', [$server, $site]);
    }
}
