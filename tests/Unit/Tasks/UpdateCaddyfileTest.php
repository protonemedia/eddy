<?php

namespace Tests\Unit\Tasks;

use App\Tasks\UpdateCaddyfile;
use Database\Factories\SiteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateCaddyfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_formats_the_caddyfile_and_then_replaces_the_current_one_and_reloads_caddy()
    {
        $site = SiteFactory::new()->installed()->create([
            'address' => 'protone.dev',
        ]);

        Str::createRandomStringsUsing(fn () => '0IJ7PAPA18XR05Jt');

        $this->assertMatchesBashSnapshot(
            (new UpdateCaddyfile($site, 'new content'))->getScript()
        );
    }
}
