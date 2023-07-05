<PrismEditor
    {{ $attributes->merge([
        'name' => $name,
        'v-model' => $vueModel(),
        'data-validation-key' => $validationKey(),
    ])->except('class') }}
    #default="{ prism }"
>
    <div {{ $attributes->only('class') }}>
        <label class="block">
            @includeWhen($label, 'splade::form.label', ['label' => $label])
            <component :is="prism" />
        </label>

        @includeWhen($help, 'splade::form.help', ['help' => $help])
        @includeWhen($showErrors, 'splade::form.error', ['name' => $validationKey()])
    </div>
</PrismEditor>
