<?php

namespace Tests\Unit\Tasks;

use App\Tasks\HasCallbacks;
use App\Tasks\Task;
use Database\Factories\TaskFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class TaskWithCallbackTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_a_http_request_as_callback()
    {
        /** @var \App\Models\Task */
        $taskModel = TaskFactory::new()->create();

        $callbackUrl = $taskModel->callbackUrl();

        $task = new class extends Task implements HasCallbacks
        {
            public function render()
            {
                return Blade::render('
echo "Start"
<x-task-callback :url="$callbackUrl()" />
<x-task-callback :url="$callbackUrl()" :data="(1)" />
<x-task-callback :url="$callbackUrl()" :data="(\'string\')" />
<x-task-callback :url="$callbackUrl()" :data="([\'foo\' => \'bar\'])" />
                ', $this->getData());
            }
        };

        $rendered = $task->setTaskModel($taskModel)->render();

        $this->assertStringContainsString(
            "httpPostSilently {$callbackUrl} 'null'",
            $rendered
        );

        $this->assertStringContainsString(
            "httpPostSilently {$callbackUrl} '1'",
            $rendered
        );

        $this->assertStringContainsString(
            "httpPostSilently {$callbackUrl} '\"string\"'",
            $rendered
        );

        $this->assertStringContainsString(
            "httpPostSilently {$callbackUrl} '{\"foo\":\"bar\"}'",
            $rendered
        );
    }
}
