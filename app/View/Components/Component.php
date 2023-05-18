<?php

namespace App\View\Components;

use App\Tasks\Formatter;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component as BaseComponent;

abstract class Component extends BaseComponent
{
    /**
     * Helper method to fluently create and render the component.
     *
     * @param  mixed  ...$parameters
     */
    public static function build(...$parameters): string
    {
        /** @phpstan-ignore-next-line */
        $component = new static(...$parameters);
        $rendered = $component->renderComponent();

        /** @var Formatter */
        $formatter = app(Formatter::class);

        if ($component instanceof BashScript) {
            $rendered = $formatter->bash($rendered);
        }

        if ($component instanceof Caddyfile) {
            $rendered = $formatter->caddyfile($rendered);
        }

        return $rendered;
    }

    /**
     * Helper method to fluently render the component.
     */
    public function renderComponent(): string
    {
        return Blade::renderComponent($this);
    }
}
