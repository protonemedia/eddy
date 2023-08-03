<?php

namespace App;

use App\Models\Certificate;
use App\Models\Site;
use App\Models\TlsSetting;
use App\Server\PhpVersion;
use Exception;

class CaddyfilePatcher
{
    /**
     * It assumes that the Caddyfile is already prettified.
     */
    public function __construct(
        public Site $site,
        private string $caddyfile,
    ) {
    }

    /**
     * Replace the PHP socket in the Caddyfile.
     */
    public function replacePhpVersion(PhpVersion $newPhpVersion): self
    {
        $regex = '/php_fastcgi\s+unix\/\/run\/php\/php\d\.\d-fpm.sock/';

        $this->caddyfile = preg_replace($regex, "php_fastcgi unix/{$newPhpVersion->getSocket()}", $this->caddyfile);

        return $this;
    }

    /**
     * Replace the root path in the Caddyfile.
     */
    public function replacePublicFolder(string $newPublicFolder): self
    {
        $regex = '/root\s+\*\s+.*$/m';

        $this->caddyfile = preg_replace($regex, "root * {$newPublicFolder}", $this->caddyfile);

        return $this;
    }

    /**
     * Replace the port in the Caddyfile.
     */
    public function replacePort(int $newPort): self
    {
        $this->caddyfile = str_replace(
            "{$this->site->address}:{$this->site->port}",
            "{$this->site->address}:{$newPort}",
            $this->caddyfile
        );

        if ($this->site->startsWithWww()) {
            $addressWithoutWww = substr($this->site->address, 4);

            $this->caddyfile = str_replace(
                "{$addressWithoutWww}:{$this->site->port}",
                "{$addressWithoutWww}:{$newPort}",
                $this->caddyfile
            );
        }

        return $this;
    }

    /**
     * Replaces the TLS snippet for the site with a new one.
     */
    public function replaceTlsSnippet(TlsSetting $newTlsSetting, Certificate $certificate = null): self
    {
        $lines = explode(PHP_EOL, $this->caddyfile);

        $startLine = array_search("(tls-{$this->site->id}) {", $lines);

        if ($startLine === false) {
            throw new Exception("Failed to find the start line of the TLS snippet for site {$this->site->id}.");
        }

        $lines = collect($lines);

        $endLine = $lines->search(function ($value, $key) use ($startLine) {
            if ($key < $startLine) {
                return false;
            }

            // We're looking for the closing tag of the TLS snippet, which
            // doesn't start with a space.
            return $value === '}';
        });

        if ($endLine === false) {
            throw new Exception("Failed to find the end line of the TLS snippet for site {$this->site->id}.");
        }

        $oldSnippet = $lines->slice($startLine, $endLine - $startLine + 1)->implode("\n");

        $newSnippet = view('components.server.site-tls-snippet', [
            'site' => $this->site,
            'tlsSetting' => $newTlsSetting,
            'certificate' => $certificate,
        ])->render();

        $this->caddyfile = str_replace($oldSnippet, $newSnippet, $this->caddyfile);

        return $this;
    }

    /**
     * Returns the patched Caddyfile.
     */
    public function get(): string
    {
        return $this->caddyfile;
    }
}
