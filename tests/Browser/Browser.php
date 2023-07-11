<?php

namespace Tests\Browser;

use Illuminate\Support\Str;
use Laravel\Dusk\Browser as BaseBrowser;

class Browser extends BaseBrowser
{
    /**
     * Helper methods that checks whether the select field is a Choices.js
     * field and if so, it uses the choicesSelect() macro that comes
     * with Splade. Otherwise, it uses the default select() method.
     */
    public function select($field, $value = null): self
    {
        $choicesSelector = Str::startsWith($field, '@')
            ? '[dusk="'.explode('@', $field)[1].'"]'
            : 'div.choices__inner[data-select-name="'.$field.'"]';

        $formattedChoicesSelector = $this->resolver->format($choicesSelector);

        $dataType = $this->script("return document.querySelector('{$formattedChoicesSelector}').parentNode.getAttribute('data-type');")[0] ?? false;

        if ($dataType) {
            $this->choicesSelect($field, $value);

            return $this;
        }

        return parent::select($field, $value = null);
    }

    /**
     * Helper method that waits for the Headless UI modal to be opened.
     */
    public function waitForModal(): self
    {
        return $this
            ->waitFor('#headlessui-portal-root')
            ->waitFor('div[data-headlessui-state="open"]');
    }

    /**
     * Clear and type a value into a field.
     */
    public function clearAndType(string $field, string $value): self
    {
        return $this->clear($field)->pause(150)->type($field, $value);
    }
}
