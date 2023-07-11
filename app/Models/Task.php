<?php

namespace App\Models;

use App\Jobs\UpdateTaskOutput;
use App\Tasks\CallbackType;
use App\Tasks\GetFile;
use App\Tasks\HasCallbacks;
use Exception;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelTaskRunner\ProcessOutput;

/**
 * @property Server $server
 */
class Task extends Model
{
    use HasFactory;
    use HasUlids;

    protected $casts = [
        'exit_code' => 'integer',
        'instance' => 'encrypted',
        'output' => 'encrypted',
        'script' => 'encrypted',
        'status' => TaskStatus::class,
        'timeout' => 'integer',
    ];

    protected $guarded = [];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Returns a boolean whether the task is past its timeout.
     */
    public function isOlderThanTimeout(): bool
    {
        return $this->created_at->copy()->addSeconds($this->timeout)->isPast();
    }

    /**
     * Returns the path to the task's output file on the server.
     */
    public function outputLogPath(): string
    {
        $directory = $this->user === 'root'
            ? $this->server->connectionAsRoot()->scriptPath
            : $this->server->connectionAsUser()->scriptPath;

        return "{$directory}/task-{$this->id}.log";
    }

    /**
     * It downloads the output file from the server, stores it
     * in the database, and handles any callbacks.
     */
    public function updateOutput(bool $handleCallbacks = true): self
    {
        /** @var ProcessOutput */
        $output = $this->server->runTask(new GetFile($this->outputLogPath()))->asRoot()->dispatch();

        if (! $output->isSuccessful()) {
            throw new Exception('Failed to download task log');
        }

        $this->update([
            'output' => $output = $output->getBuffer(),
        ]);

        if ($handleCallbacks && $instance = unserialize($this->instance)) {
            /** @var \App\Tasks\Task $instance */
            $instance->setTaskModel($this)->onOutputUpdated($output);
        }

        return $this;
    }

    /**
     * Same as updateOutput() but without the callback handling.
     */
    public function updateOutputWithoutCallbacks(): self
    {
        return $this->updateOutput(handleCallbacks: false);
    }

    /**
     * Dispatches a job that updates the task's output.
     */
    public function updateOutputInBackground(): self
    {
        UpdateTaskOutput::dispatch($this);

        return $this;
    }

    /**
     * Returns the output as a collection of lines.
     */
    public function outputLines(): Collection
    {
        return Collection::make(explode(PHP_EOL, $this->output));
    }

    /**
     * Return the last given number of lines.
     */
    public function tailOutput(int $lines = 10): string
    {
        return $this->outputLines()->take($lines * -1)->implode(PHP_EOL);
    }

    /**
     * Generates a signed URL that may be called to trigger a callback.
     */
    private function webhookUrl(string $name): string
    {
        $name = Str::kebab($name);

        return URL::relativeSignedRoute('webhook.task.'.$name, ['task' => $this->id]);
    }

    public function timeoutUrl(): string
    {
        return $this->webhookUrl('markAsTimedOut');
    }

    public function failedUrl(): string
    {
        return $this->webhookUrl('markAsFailed');
    }

    public function finishedUrl(): string
    {
        return $this->webhookUrl('markAsFinished');
    }

    public function callbackUrl(): string
    {
        return $this->webhookUrl('callback');
    }

    /**
     * Calls the callback handler on the Task instance.
     */
    public function handleCallback(Request $request, CallbackType $type)
    {
        $instance = unserialize($this->instance);

        if ($instance instanceof HasCallbacks) {
            $instance->handleCallback($this, $request, $type);
        }
    }
}
