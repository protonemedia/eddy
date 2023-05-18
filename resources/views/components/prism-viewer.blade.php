<PrismEditor {{ $attributes->except('class') }} #default="{ prism }" :modelValue="@js($value)" :disabled="true">
    <div {{ $attributes->only('class') }}>
        <label class="block relative">
            @includeWhen($label, 'splade::form.label', ['label' => $label])
            <component :is="prism" />

            @if($copyToClipboard)
                <div class="absolute bottom-0 right-0">
                    <x-clipboard class="w-4 h-4 mr-1">{{ $value }}</x-clipboard>
                </div>
            @endif
        </label>

        @includeWhen($help, 'splade::form.help', ['help' => $help])
    </div>
</PrismEditor>