<?php

namespace App;

use App\Models\Server;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FileOnServer
{
    public function __construct(
        public string $name,
        public string $description,
        public string $path,
        public ?PrismLanguage $prismLanguage = null,
        public ?string $context = null,
        public ?ValidationRule $validationRule = null,
        public ?Closure $afterUpdating = null,
    ) {
        $this->prismLanguage ??= PrismLanguage::Clike;
    }

    public function context(string $context = null): self
    {
        $this->context = $context;

        return $this;
    }

    public function nameWithContext(): string
    {
        $name = $this->name;

        if ($this->context) {
            $name .= " ({$this->context})";
        }

        return $name;
    }

    public function showRoute(Server $server): string
    {
        return route('servers.files.show', [$server, $this->routeParameter()]);
    }

    public function editRoute(Server $server): string
    {
        return route('servers.files.edit', [$server, $this->routeParameter()]);
    }

    public function updateRoute(Server $server): string
    {
        return route('servers.files.update', [$server, $this->routeParameter()]);
    }

    public function routeParameter(): string
    {
        return encrypt(gzencode($this->path, 9));
    }

    public static function pathFromRouteParameter(string $parameter): string
    {
        return gzdecode(decrypt($parameter));
    }
}
