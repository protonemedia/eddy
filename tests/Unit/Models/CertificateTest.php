<?php

namespace Tests\Unit\Models;

use App\Models\Certificate;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_it_can_return_site_directory_path()
    {
        $site = Site::factory()->create(['path' => '/var/www/example.com']);
        $certificate = Certificate::factory()->create(['site_id' => $site->id]);

        $this->assertEquals('/var/www/example.com/certificates/'.$certificate->id, $certificate->siteDirectory());
    }

    /** @test */
    public function it_can_it_can_return_certificate_path()
    {
        $site = Site::factory()->create(['path' => '/var/www/example.com']);
        $certificate = Certificate::factory()->create(['site_id' => $site->id]);

        $this->assertEquals('/var/www/example.com/certificates/'.$certificate->id.'/certificate.cert', $certificate->certificatePath());
    }

    /** @test */
    public function it_can_it_can_return_private_key_path()
    {
        $site = Site::factory()->create(['path' => '/var/www/example.com']);
        $certificate = Certificate::factory()->create(['site_id' => $site->id]);

        $this->assertEquals('/var/www/example.com/certificates/'.$certificate->id.'/private.key', $certificate->privateKeyPath());
    }
}
