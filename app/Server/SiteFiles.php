<?php

namespace App\Server;

use App\FileOnServer;
use App\Models\Site;
use App\Models\SiteType;
use App\PrismLanguage;
use App\Rules\CaddyfileOnServer;
use App\Tasks\ReloadCaddy;
use Illuminate\Support\Collection;

class SiteFiles
{
    public function __construct(private Site $site)
    {
        $this->site = $site;
    }

    /**
     * The Caddyfile for this site.
     */
    public function caddyfile(): FileOnServer
    {
        return new FileOnServer(
            'Caddyfile',
            __('The configuration file for Caddy. It is used to configure your site(s), including how to handle requests, TLS certificates, and more.'),
            "{$this->site->path}/Caddyfile",
            PrismLanguage::Nginx,
            $this->site->address,
            new CaddyfileOnServer($this->site->server),
            fn () => $this->site->server->runTask(ReloadCaddy::class)->asRoot()->inBackground()->dispatch(),
        );
    }

    /**
     * The .env file for this site.
     */
    public function environmentFile(): FileOnServer
    {
        return new FileOnServer(
            __('Environment file'),
            __('The environment file for your site. It contains environment variables that are available to your site.'),
            $this->site->zero_downtime_deployment ? "{$this->site->path}/shared/.env" : "{$this->site->path}/repository/.env",
            PrismLanguage::Clike,
            context: $this->site->address
        );
    }

    /**
     * The WordPress wp-config.php file.
     */
    public function wordpressConfig(): FileOnServer
    {
        return new FileOnServer(
            __('Wordpress config'),
            __('The configuration file for Wordpress. It contains database credentials and other settings.'),
            $this->site->zero_downtime_deployment ? "{$this->site->path}/shared/wp-config.php" : "{$this->site->path}/repository/wp-config.php",
            PrismLanguage::Php,
            context: $this->site->address
        );
    }

    /**
     * Returns a collection of all log files for this site.
     */
    public function logFiles(): Collection
    {
        $logs = Collection::make([
            new FileOnServer(
                __('Caddy Log'),
                __('The Caddy Log contains all the requests that are made to your site.'),
                $this->site->getLogsDirectory().'/caddy.log'
            ),
        ]);

        return $logs->each(fn (FileOnServer $fileOnServer) => $fileOnServer->context($this->site->address));
    }

    /**
     * Returns a collection of all editable files for this site.
     */
    public function editableFiles(): Collection
    {
        $logs = Collection::make([
            $this->caddyfile(),
        ]);

        if ($this->site->type === SiteType::Wordpress) {
            $logs->push($this->wordpressConfig());
        } else {
            $logs->push($this->environmentFile());
        }

        return $logs->each(fn (FileOnServer $fileOnServer) => $fileOnServer->context($this->site->address));
    }
}
