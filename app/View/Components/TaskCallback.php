<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskCallback extends Component
{
    public string $body;

    public string $bashFunction = 'httpPostSilently';

    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?string $url = null,
        mixed $data = null,
        string $raw = ''
    ) {
        if (is_string($data) && trim($data) === '') {
            $data = null;
        }

        if (trim($raw) !== '') {
            $this->body = '"'.$raw.'"';
            $this->bashFunction = 'httpPostRawSilently';
        } else {
            $this->body = "'".json_encode($data)."'";
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return $this->url ? view('components.task-callback') : PHP_EOL;
    }
}
