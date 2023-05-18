<?php

namespace App\View\Components;

use App\Models\Server;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class ServerLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public Server $server, public string $title = '')
    {
    }

    public function href(NavigationItem $item): string
    {
        return $item->href($this->server);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.server-layout', [
            'server' => $this->server,
            'navigationItems' => [
                new NavigationItem(__('Overview'), route('servers.show', $this->server), 'heroicon-o-server'),
                new NavigationItem(__('Sites'), 'servers.sites', 'heroicon-o-list-bullet'),
                new NavigationItem(__('Databases'), 'servers.databases', 'heroicon-o-circle-stack'),
                new NavigationItem(__('Cronjobs'), 'servers.crons', 'heroicon-o-clock'),
                new NavigationItem(__('Daemons'), 'servers.daemons', 'heroicon-o-arrow-path'),
                new NavigationItem(__('Firewall Rules'), 'servers.firewall-rules', 'heroicon-o-shield-check'),
                new NavigationItem(__('Software'), 'servers.software', 'heroicon-o-code-bracket'),
                new NavigationItem(__('Files'), 'servers.files', 'heroicon-o-document-text'),
                new NavigationItem(__('Logs'), 'servers.logs', 'heroicon-o-book-open'),
            ],
        ]);
    }
}
