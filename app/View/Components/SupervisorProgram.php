<?php

namespace App\View\Components;

use App\Models\Daemon;

class SupervisorProgram extends Component implements BashScript
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public Daemon $daemon)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.server.supervisor-program');
    }
}
