@seoTitle(__('Add SSH Key'))

<x-app-layout>
    <x-action-section>
        <x-slot:title>
            {{ __('Add SSH Key') }}
        </x-slot>

        <x-slot:description>
            {{ __('Add new SSH Key.') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form :action="route('ssh-keys.store')" class="space-y-4">
                <x-splade-input name="name" :label="__('Name')" />

                <x-splade-textarea
                    autosize
                    name="public_key"
                    :label="__('Public Key')"
                />

                <x-splade-submit />
            </x-splade-form>
        </x-slot>
    </x-action>
</x-app-layout>