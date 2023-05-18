<?php

namespace Tests\Unit\Server;

use App\FileOnServer;
use App\Models\Server;
use App\Models\Site;
use App\Server\ServerFiles;
use App\Server\Software;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerFilesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_return_log_files()
    {
        /** @var Server */
        $server = Server::factory()->create();
        $serverFiles = $server->files();

        $expectedFiles = [
            '/var/log/caddy/access.log',
            '/var/log/caddy/error.log',
            '/var/log/mysql/error.log',
            '/var/log/redis/redis-server.log',
        ];

        $this->assertSame($expectedFiles, $serverFiles->logFiles()->pluck('path')->toArray());
    }

    /** @test */
    public function it_can_return_php81_fpm_log_file_exists_on_server()
    {
        /** @var Server */
        $server = Server::factory()->create(['installed_software' => []]);
        $server->installed_software[] = Software::Php81->value;
        $server->save();

        $serverFiles = $server->files();
        $logFiles = $serverFiles->logFiles();

        $php81FpmLogFile = $logFiles->firstWhere('name', 'PHP 8.1 FPM Log');

        $this->assertNotNull($php81FpmLogFile);
        $this->assertEquals('/var/log/php8.1-fpm.log', $php81FpmLogFile->path);
    }

    /** @test */
    public function it_can_return_php82_fpm_log_file_exists_on_server()
    {
        /** @var Server */
        $server = Server::factory()->create(['installed_software' => []]);
        $server->installed_software[] = Software::Php82->value;
        $server->save();

        $serverFiles = $server->files();
        $logFiles = $serverFiles->logFiles();

        $php82FpmLogFile = $logFiles->firstWhere('name', 'PHP 8.2 FPM Log');

        $this->assertNotNull($php82FpmLogFile);
        $this->assertEquals('/var/log/php8.2-fpm.log', $php82FpmLogFile->path);
    }

    /** @test */
    public function it_can_return_editable_files_contains_php_ini_and_php_fpm_conf_for_php81()
    {
        /** @var Server */
        $server = Server::factory()->create(['installed_software' => []]);
        $server->installed_software[] = Software::Php81->value;
        $server->save();

        $serverFiles = $server->files();
        $editableFiles = $serverFiles->editableFiles();

        $phpIniFile = $editableFiles->firstWhere('path', '/etc/php/8.1/fpm/php.ini');
        $phpFpmConfFile = $editableFiles->firstWhere('path', '/etc/php/8.1/fpm/php-fpm.conf');

        $this->assertInstanceOf(FileOnServer::class, $phpIniFile);
        $this->assertInstanceOf(FileOnServer::class, $phpFpmConfFile);
    }

    /** @test */
    public function it_can_return_editable_files_contains_php_ini_and_php_fpm_conf_for_php82()
    {
        /** @var Server */
        $server = Server::factory()->create(['installed_software' => []]);
        $server->installed_software[] = Software::Php82->value;
        $server->save();

        $serverFiles = $server->files();
        $editableFiles = $serverFiles->editableFiles();

        $phpIniFile = $editableFiles->firstWhere('path', '/etc/php/8.2/fpm/php.ini');
        $phpFpmConfFile = $editableFiles->firstWhere('path', '/etc/php/8.2/fpm/php-fpm.conf');

        $this->assertInstanceOf(FileOnServer::class, $phpIniFile);
        $this->assertInstanceOf(FileOnServer::class, $phpFpmConfFile);
    }

    /** @test */
    public function it_can_return_editable_files_contains_composer_auth_json_if_installed()
    {
        /** @var Server */
        $server = Server::factory()->create(['installed_software' => []]);
        $server->installed_software[] = Software::Composer2->value;
        $server->save();

        $serverFiles = $server->files();
        $editableFiles = $serverFiles->editableFiles();

        $composerAuthFile = $editableFiles->firstWhere('path', '/home/eddy/.config/composer/auth.json');

        $this->assertInstanceOf(FileOnServer::class, $composerAuthFile);
    }

    /** @test */
    public function it_can_return_all_log_files()
    {
        $site = Site::factory()->create();
        $server = $site->server;
        $server->installed_software[] = Software::Php82->value;
        $server->save();

        $logs = new ServerFiles($server);
        $allLogs = $logs->allLogFiles();

        $this->assertNotEmpty($allLogs);
        $phpLogFile = $allLogs->firstWhere('path', '/var/log/php8.2-fpm.log');
        $siteCaddyLog = $allLogs->firstWhere('path', "/home/eddy/{$site->address}/logs/caddy.log");

        $this->assertInstanceOf(FileOnServer::class, $phpLogFile);
        $this->assertInstanceOf(FileOnServer::class, $siteCaddyLog);
    }

    /** @test */
    public function it_can_return_all_editable_files()
    {
        $site = Site::factory()->create();
        $server = $site->server;
        $server->installed_software[] = Software::Php82->value;
        $server->save();

        $editable = new ServerFiles($server);
        $allEditable = $editable->allEditableFiles();

        $this->assertNotEmpty($allEditable);
        $serverCaddyfile = $allEditable->firstWhere('path', '/etc/caddy/Caddyfile');
        $siteEnvFile = $allEditable->firstWhere('path', "/home/eddy/{$site->address}/shared/.env");

        $this->assertInstanceOf(FileOnServer::class, $serverCaddyfile);
        $this->assertInstanceOf(FileOnServer::class, $siteEnvFile);
    }
}
