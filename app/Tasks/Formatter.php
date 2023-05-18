<?php

namespace App\Tasks;

use Illuminate\Support\Facades\Process;
use ProtoneMedia\LaravelTaskRunner\ProcessRunner;

class Formatter
{
    /**
     * Format the given bash script.
     */
    public function bash(string $script): string
    {
        return $this->handle($script, fn (string $path) => "beautysh {$path} -i 4");
    }

    /**
     * Format the given Caddyfile.
     */
    public function caddyfile(string $caddyfile): string
    {
        return $this->handle($caddyfile, fn (string $path) => "caddy fmt {$path} --overwrite");
    }

    /**
     * Format the given content with the command from the callback.
     */
    private function handle(string $content, callable $commandCallback): string
    {
        if (! config('eddy.format_server_content')) {
            return $content;
        }

        // Store the content in a temporary file
        $temporaryFile = tempnam(sys_get_temp_dir(), 'beautify');

        file_put_contents($temporaryFile, $content);

        // Resolve the command
        $command = $commandCallback($temporaryFile);

        // Run the command and return the original content if it fails
        $output = (new ProcessRunner)->run(Process::command($command)->timeout(15));

        if (! $output->isSuccessful()) {
            unlink($temporaryFile);

            return $content;
        }

        // Get the formatted content from the temporary file
        $content = file_get_contents($temporaryFile);

        unlink($temporaryFile);

        // Remove multiple empty lines
        $content = preg_replace('"(\r?\n){2,}"', "\n\n", $content);

        return $content;
    }
}
