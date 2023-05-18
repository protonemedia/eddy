<?php

namespace App\View\Components;

use App\Models\Site;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class SiteLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public Site $site, public string $title = '')
    {
        //
    }

    public function href(NavigationItem $item): string
    {
        return $item->href($this->site->server, $this->site);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $parameters = [$this->site->server, $this->site];

        return view('components.site-layout', [
            'server' => $this->site->server,
            'site' => $this->site,
            'navigationItems' => [
                new NavigationItem(__('Overview'), route('servers.sites.show', $parameters), 'heroicon-o-globe-alt'),
                new NavigationItem(__('Deployments'), 'servers.sites.deployments', 'heroicon-o-square-3-stack-3d'),
                new NavigationItem(__('Site Settings'), route('servers.sites.edit', $parameters), 'heroicon-o-cog-6-tooth'),
                new NavigationItem(__('Deployment Settings'), route('servers.sites.deployment-settings.edit', $parameters), 'heroicon-o-wrench-screwdriver'),
                new NavigationItem(__('SSL'), route('servers.sites.ssl.edit', $parameters), 'heroicon-o-lock-closed'),
                new NavigationItem(__('Files'), 'servers.sites.files', 'heroicon-o-document-text'),
                new NavigationItem(__('Logs'), 'servers.sites.logs', 'heroicon-o-book-open'),
            ],
        ]);
    }
}
