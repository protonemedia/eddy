<?php

namespace App\View\Components;

use App\Models\Site;

class SiteCaddyfile extends Component implements Caddyfile
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public Site $site)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.server.site-caddyfile');
    }
}
