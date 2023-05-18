<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskShellDefaults extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public bool $exitImmediately = true)
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $setOptions = array_filter([
            $this->exitImmediately ? 'e' : null,
            'u',
            config('eddy.print_shell_commands') ? 'x' : null,
        ]);

        return view('components.task-shell-defaults', [
            'setOptions' => implode($setOptions),
        ]);
    }
}
