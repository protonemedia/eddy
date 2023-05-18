<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Spatie\Snapshots\MatchesSnapshots;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use MatchesSnapshots;

    public function assertMatchesBashSnapshot($actual): void
    {
        $this->assertMatchesSnapshot($actual, new SnapshotBashDriver());
    }

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(now());

        config([
            // Set the app key to a fixed value so that the signed URL is always the same
            'app.key' => 'base64:p9hXSwierRvJZo1VrOkOZE5Q3HqvOQPZb83T7Z56x1E=',
            'eddy.webhook_url' => 'https://webhook.app/',
        ]);

        Http::preventStrayRequests();
        Process::preventStrayProcesses();
        $this->withoutVite();
    }
}
