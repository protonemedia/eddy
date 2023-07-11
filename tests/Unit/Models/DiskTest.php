<?php

namespace Tests\Unit\Models;

use App\Models\Disk;
use App\Models\Server;
use Database\Factories\DiskFactory;
use Database\Factories\ServerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_formats_the_s3_disk_into_a_json_format_for_the_cli_tool()
    {
        /** @var Server */
        $server = ServerFactory::new()->create();

        /** @var Disk */
        $disk = DiskFactory::new()->s3()->create();

        $this->assertMatchesSnapshot($disk->configurationForStorageBuilder($server));
    }

    /** @test */
    public function it_formats_the_sftp_disk_into_a_json_format_for_the_cli_tool()
    {
        /** @var Server */
        $server = ServerFactory::new()->create();

        /** @var Disk */
        $disk = DiskFactory::new()->sftp()->create();

        $this->assertMatchesSnapshot($disk->configurationForStorageBuilder($server));
    }

    /** @test */
    public function it_formats_the_sftp_disk_with_ssh_key_auth_into_a_json_format_for_the_cli_tool()
    {
        /** @var Server */
        $server = ServerFactory::new()->create();

        /** @var Disk */
        $disk = DiskFactory::new()->sftpSsh()->create();

        $this->assertMatchesSnapshot($disk->configurationForStorageBuilder($server));
    }

    /** @test */
    public function it_formats_the_ftp_disk_into_a_json_format_for_the_cli_tool()
    {
        /** @var Server */
        $server = ServerFactory::new()->create();

        /** @var Disk */
        $disk = DiskFactory::new()->ftp()->create();

        $this->assertMatchesSnapshot($disk->configurationForStorageBuilder($server));
    }
}
