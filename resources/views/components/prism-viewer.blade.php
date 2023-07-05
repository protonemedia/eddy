<PrismEditor {{ $attributes->except('class') }} #default="{ prism }" :modelValue="@js($value)" :disabled="true">
    <div {{ $attributes->only('class') }}>
        <label class="relative block">
            @includeWhen($label, 'splade::form.label', ['label' => $label])
            <component :is="prism" />

            @if ($copyToClipboard)
                <div class="absolute bottom-0 right-0">
                    <x-clipboard class="mr-1 h-4 w-4">{{ $value }}</x-clipboard>
                </div>
            @endif
        </label>

        @includeWhen($help, 'splade::form.help', ['help' => $help])
    </div>
</PrismEditor>
