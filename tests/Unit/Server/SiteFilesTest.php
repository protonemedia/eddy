<?php

namespace Tests\Unit\Server;

use Database\Factories\SiteFactory;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SiteFilesTest extends TestCase
{
    /** @test */
    public function it_can_return_caddyfile()
    {
        $site = SiteFactory::new()->create();
        $siteFiles = $site->files();

        $caddyfile = $siteFiles->caddyfile();

        $this->assertEquals('Caddyfile', $caddyfile->name);
        $this->assertEquals("{$site->path}/Caddyfile", $caddyfile->path);
    }

    /** @test */
    public function it_can_return_environment_file_with_zero_downtime_deployment()
    {
        $site = SiteFactory::new()->create(['zero_downtime_deployment' => true]);
        $siteFiles = $site->files();

        $envFile = $siteFiles->environmentFile();

        $this->assertEquals('Environment file', $envFile->name);
        $this->assertEquals("{$site->path}/shared/.env", $envFile->path);
    }

    /** @test */
    public function it_can_return_environment_file_without_zero_downtime_deployment()
    {
        $site = SiteFactory::new()->create(['zero_downtime_deployment' => false]);
        $siteFiles = $site->files();

        $envFile = $siteFiles->environmentFile();

        $this->assertEquals('Environment file', $envFile->name);
        $this->assertEquals("{$site->path}/repository/.env", $envFile->path);
    }

    /** @test */
    public function it_can_return_log_files_returns_collection_of_files()
    {
        $site = SiteFactory::new()->create();
        $siteFiles = $site->files();

        $logFiles = $siteFiles->logFiles();

        $this->assertInstanceOf(Collection::class, $logFiles);
        $this->assertCount(1, $logFiles);

        $this->assertEquals('Caddy Log', $logFiles[0]->name);
        $this->assertEquals("/home/eddy/{$site->address}/logs/caddy.log", $logFiles[0]->path);
    }

    /** @test */
    public function it_can_return_editable_files_returns_collection_of_files()
    {
        $site = SiteFactory::new()->create();
        $siteFiles = $site->files();

        $editableFiles = $siteFiles->editableFiles();

        $this->assertInstanceOf(Collection::class, $editableFiles);
        $this->assertCount(2, $editableFiles);

        $this->assertEquals('Caddyfile', $editableFiles[0]->name);
        $this->assertStringContainsString('Caddyfile', $editableFiles[0]->path);

        $this->assertEquals('Environment file', $editableFiles[1]->name);
        $this->assertStringContainsString('.env', $editableFiles[1]->path);
    }
}
