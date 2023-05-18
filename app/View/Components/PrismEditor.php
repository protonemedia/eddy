<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use ProtoneMedia\Splade\Components\Form\Textarea;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class PrismEditor extends Textarea
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.prism-editor');
    }
}
