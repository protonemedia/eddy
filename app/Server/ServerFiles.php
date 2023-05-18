<?php

namespace App\Server;

use App\FileOnServer;
use App\Models\Server;
use App\Models\Site;
use App\PrismLanguage;
use App\Rules\CaddyfileOnServer;
use App\Rules\JsonString;
use App\Rules\MySqlConfigOnServer;
use App\Tasks\ReloadCaddy;
use App\Tasks\RestartMySql;
use App\Tasks\RestartPhp81;
use App\Tasks\RestartPhp82;
use Illuminate\Support\Collection;

class ServerFiles
{
    public function __construct(private Server $server)
    {
        $this->server = $server;
    }

    /**
     * The 'global' Caddyfile.
     */
    public function caddyfile(): FileOnServer
    {
        return new FileOnServer(
            'Caddyfile',
            __('The configuration file for Caddy. It is used to configure your site(s), including how to handle requests, TLS certificates, and more.'),
            '/etc/caddy/Caddyfile',
            PrismLanguage::Nginx,
            $this->server->name,
            new CaddyfileOnServer($this->server),
            fn () => $this->server->runTask(ReloadCaddy::class)->asRoot()->inBackground()->dispatch(),
        );
    }

    /**
     * The my.cnf file.
     */
    public function mysqlConfigFile(): FileOnServer
    {
        return new FileOnServer(
            __('MySQL config file'),
            __('The MySQL configuration file. It is used to configure MySQL\'s behavior.'),
            '/etc/mysql/my.cnf',
            PrismLanguage::Clike,
            $this->server->name,
            new MySqlConfigOnServer($this->server),
            fn () => $this->server->runTask(RestartMySql::class)->asRoot()->inBackground()->dispatch(),
        );
    }

    /**
     * Returns a collection of all log files on the server.
     */
    public function logFiles(): Collection
    {
        $logs = Collection::make([
            new FileOnServer(
                __('Caddy Access Log'),
                __('The Caddy Access Log tracks every request made to your website, including details about the user, data accessed, and timing. This information helps you monitor traffic, user activity, and troubleshoot issues.'),
                '/var/log/caddy/access.log'
            ),
            new FileOnServer(
                __('Caddy Error Log'),
                __('The Caddy Error Log records all errors encountered while serving your website, allowing you to quickly identify and fix any issues that may affect user experience.'),
                '/var/log/caddy/error.log'
            ),
            new FileOnServer(
                __('MySQL Error Log'),
                __('The MySQL Error Log documents all errors encountered while running the MySQL server, providing a valuable tool for troubleshooting and resolving issues with the database'),
                '/var/log/mysql/error.log'
            ),
            new FileOnServer(
                __('Redis Server Log'),
                __('The Redis Server Log keeps track of all requests made to the Redis server, including operations performed, data accessed, and user information. This log helps you monitor performance and troubleshoot issues.'),
                '/var/log/redis/redis-server.log'
            ),
        ]);

        if ($this->server->softwareIsInstalled(Software::Php81)) {
            $logs[] = new FileOnServer(
                __('PHP 8.1 FPM Log'),
                __('The PHP 8.1 FPM Log documents all requests made to the PHP 8.1 FastCGI Process Manager, allowing you to monitor performance and identify any issues that may arise.'),
                '/var/log/php8.1-fpm.log'
            );
        }

        if ($this->server->softwareIsInstalled(Software::Php82)) {
            $logs[] = new FileOnServer(
                __('PHP 8.2 FPM Log'),
                __('The PHP 8.2 FPM Log documents all requests made to the PHP 8.2 FastCGI Process Manager, allowing you to monitor performance and identify any issues that may arise.'),
                '/var/log/php8.2-fpm.log'
            );
        }

        return $logs->each(fn (FileOnServer $fileOnServer) => $fileOnServer->context($this->server->name));
    }

    /**
     * Returns a collection of all log files, including the sites' logs.
     */
    public function allLogFiles(): Collection
    {
        return $this->logFiles()->merge(
            $this->server->sites->map(fn (Site $site) => $site->files()->logFiles())->flatten()
        );
    }

    /**
     * Returns a collection of all config files on the server.
     */
    public function editableFiles(): Collection
    {
        $files = Collection::make([
            $this->caddyfile(),
            $this->mysqlConfigFile(),
        ]);

        if ($this->server->softwareIsInstalled(Software::Php81)) {
            $files[] = new FileOnServer(
                __('PHP 8.1 ini File'),
                __('The PHP 8.1 ini File contains configuration options for the PHP 8.1 interpreter, allowing you to customize the behavior of your PHP applications.'),
                '/etc/php/8.1/fpm/php.ini',
                afterUpdating: fn () => $this->server->runTask(RestartPhp81::class)->asRoot()->inBackground()->dispatch(),
            );

            $files[] = new FileOnServer(
                __('PHP 8.1 FPM Configuration'),
                __('The PHP 8.1 FPM Configuration file controls how PHP 8.1 processes requests. You can use this file to configure PHP 8.1 to work with your application.'),
                '/etc/php/8.1/fpm/php-fpm.conf',
                afterUpdating: fn () => $this->server->runTask(RestartPhp81::class)->asRoot()->inBackground()->dispatch(),
            );
        }

        if ($this->server->softwareIsInstalled(Software::Php82)) {
            $files[] = new FileOnServer(
                __('PHP 8.2 ini File'),
                __('The PHP 8.2 ini File contains configuration options for the PHP 8.2 interpreter, allowing you to customize the behavior of your PHP applications.'),
                '/etc/php/8.2/fpm/php.ini',
                afterUpdating: fn () => $this->server->runTask(RestartPhp82::class)->asRoot()->inBackground()->dispatch(),
            );

            $files[] = new FileOnServer(
                __('PHP 8.2 FPM Configuration'),
                __('The PHP 8.2 FPM Configuration file controls how PHP 8.2 processes requests. You can use this file to configure PHP 8.2 to work with your application.'),
                '/etc/php/8.2/fpm/php-fpm.conf',
                afterUpdating: fn () => $this->server->runTask(RestartPhp82::class)->asRoot()->inBackground()->dispatch(),
            );
        }

        if ($this->server->softwareIsInstalled(Software::Composer2)) {
            $files[] = new FileOnServer(
                __('Composer auth.json'),
                __('The Composer auth.json file contains your Composer authentication credentials, allowing you to install private packages from sources like Github and Bitbucket.'),
                "/home/{$this->server->username}/.config/composer/auth.json",
                prismLanguage: PrismLanguage::Json,
                validationRule: new JsonString
            );
        }

        return $files->each(fn (FileOnServer $fileOnServer) => $fileOnServer->context($this->server->name));
    }

    /**
     * Returns a collection of all config files, including the sites' config files.
     */
    public function allEditableFiles(): Collection
    {
        return $this->editableFiles()->merge(
            $this->server->sites->map(fn (Site $site) => $site->files()->editableFiles())->flatten()
        );
    }
}
