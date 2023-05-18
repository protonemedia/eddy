<?php

namespace Tests\Unit;

use App\CaddyfilePatcher;
use App\Models\TlsSetting;
use App\Server\PhpVersion;
use App\View\Components\SiteCaddyfile;
use Database\Factories\SiteFactory;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class CaddyfilePatcherTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new Filesystem)->delete(__DIR__.'/Caddyfile');
    }

    public function tearDown(): void
    {
        (new Filesystem)->delete(__DIR__.'/Caddyfile');

        parent::tearDown();
    }

    private function caddyfileAndSite(array $siteAttributes = []): array
    {
        $site = SiteFactory::new()->make(array_merge([
            'address' => 'protone.media',
            'id' => 1,
            'server_id' => 1,
            'php_version' => PhpVersion::Php81,
        ], $siteAttributes));

        $caddyfile = SiteCaddyfile::build($site);

        file_put_contents($path = __DIR__.'/Caddyfile', $caddyfile);
        exec("caddy fmt $path --overwrite");

        $caddyfile = file_get_contents($path);
        @unlink($path);

        return [$caddyfile, $site];
    }

    /** @test */
    public function it_can_replace_the_php_version()
    {
        [$caddyfile, $site] = $this->caddyfileAndSite();

        $patcher = new CaddyfilePatcher($site, $caddyfile);
        $newCaddyfile = $patcher->replacePhpVersion(PhpVersion::Php82)->get();

        $this->assertStringContainsString('php_fastcgi unix//run/php/php8.2-fpm.sock {', $newCaddyfile);
        $this->assertMatchesTextSnapshot($newCaddyfile);
    }

    /** @test */
    public function it_can_replace_the_root_folder()
    {
        [$caddyfile, $site] = $this->caddyfileAndSite();
        $patcher = new CaddyfilePatcher($site, $caddyfile);

        $newCaddyfile = $patcher->replacePublicFolder('/home/test/test.media/current/public')->get();

        $this->assertStringContainsString('root * /home/test/test.media/current/public', $newCaddyfile);
        $this->assertMatchesTextSnapshot($newCaddyfile);
    }

    /** @test */
    public function it_can_replace_the_https_port_to_80_without_www()
    {
        [$caddyfile, $site] = $this->caddyfileAndSite();

        $patcher = new CaddyfilePatcher($site, $caddyfile);
        $newCaddyfile = $patcher->replacePort(80)->get();

        $this->assertStringContainsString('www.protone.media:80 {', $newCaddyfile);
        $this->assertStringContainsString('protone.media:80 {', $newCaddyfile);
        $this->assertMatchesTextSnapshot($newCaddyfile);
    }

    /** @test */
    public function it_can_replace_the_http_port_to_443_without_www()
    {
        [$caddyfile, $site] = $this->caddyfileAndSite([
            'tls_setting' => TlsSetting::Off,
        ]);

        $patcher = new CaddyfilePatcher($site, $caddyfile);
        $newCaddyfile = $patcher->replacePort(443)->get();

        $this->assertStringContainsString('www.protone.media:443 {', $newCaddyfile);
        $this->assertStringContainsString('protone.media:443 {', $newCaddyfile);
        $this->assertMatchesTextSnapshot($newCaddyfile);
    }

    /** @test */
    public function it_can_replace_the_https_port_to_80_with_www()
    {
        [$caddyfile, $site] = $this->caddyfileAndSite([
            'address' => 'www.protone.media',
        ]);

        $patcher = new CaddyfilePatcher($site, $caddyfile);
        $newCaddyfile = $patcher->replacePort(80)->get();

        $this->assertStringContainsString('protone.media:80 {', $newCaddyfile);
        $this->assertStringContainsString('www.protone.media:80 {', $newCaddyfile);
        $this->assertMatchesTextSnapshot($newCaddyfile);
    }

    /** @test */
    public function it_can_replace_the_http_port_to_443_with_www()
    {
        [$caddyfile, $site] = $this->caddyfileAndSite([
            'address' => 'www.protone.media',
            'tls_setting' => TlsSetting::Off,
        ]);

        $patcher = new CaddyfilePatcher($site, $caddyfile);
        $newCaddyfile = $patcher->replacePort(443)->get();

        $this->assertStringContainsString('protone.media:443 {', $newCaddyfile);
        $this->assertStringContainsString('www.protone.media:443 {', $newCaddyfile);
        $this->assertMatchesTextSnapshot($newCaddyfile);
    }

    /** @test */
    public function it_can_replace_the_tls_snippet()
    {
        [$caddyfile, $site] = $this->caddyfileAndSite();

        $patcher = new CaddyfilePatcher($site, $caddyfile);
        $newCaddyfile = $patcher->replaceTlsSnippet(TlsSetting::Internal)->get();

        $this->assertStringContainsString('tls internal', $newCaddyfile);
        $this->assertMatchesTextSnapshot($newCaddyfile);
    }

    /** @test */
    public function it_throws_an_exception_when_the_start_line_cant_be_found()
    {
        $site = SiteFactory::new()->make([
            'address' => 'protone.media',
            'id' => 1,
            'server_id' => 1,
        ]);

        $caddyfile = '1.2.3.4 { }';

        $patcher = new CaddyfilePatcher($site, $caddyfile);

        try {
            $patcher->replaceTlsSnippet(TlsSetting::Internal)->get();
        } catch (\Exception $e) {
            return $this->assertStringContainsString('Failed to find the start line of the TLS snippet for site 1.', $e->getMessage());
        }

        $this->fail('Expected an exception to be thrown.');
    }

    /** @test */
    public function it_throws_an_exception_when_the_end_line_of_the_snippet_cant_be_found()
    {
        $site = SiteFactory::new()->make([
            'address' => 'protone.media',
            'id' => 1,
            'server_id' => 1,
        ]);

        $caddyfile = '(tls-1) {
            tls internal
        ';

        $patcher = new CaddyfilePatcher($site, $caddyfile);

        try {
            $patcher->replaceTlsSnippet(TlsSetting::Internal)->get();
        } catch (\Exception $e) {
            return $this->assertStringContainsString('Failed to find the end line of the TLS snippet for site 1.', $e->getMessage());
        }

        $this->fail('Expected an exception to be thrown.');
    }
}
