<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class PrismViewer extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public string $value = '',
        public string $label = '',
        public string $help = '',
        public bool $copyToClipboard = false
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.prism-viewer');
    }
}
