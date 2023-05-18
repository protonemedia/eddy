<?php

namespace App\View\Components;

use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class NavigationItem
{
    public bool $current = false;

    public function __construct(
        public string $label,
        public string $route,
        public string $icon
    ) {
        $this->current = Str::contains($this->route, '://')
            ? request()->fullUrlIs($this->route)
            : request()->routeIs($route.'*');
    }

    public function href(...$arguments): string
    {
        return Str::contains($this->route, '://')
            ? $this->route
            : route("{$this->route}.index", [...$arguments]);
    }
}
