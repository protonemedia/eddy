@seoTitle(__('Add SSH Key'))

<x-app-layout>
    <x-action-section>
        <x-slot:title>
            {{ __('Add SSH Key to Servers') }}
        </x-slot>

        <x-slot:description>
            {{ __('Select servers to add SSH Key to.') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form :action="route('ssh-keys.servers.add', $sshKey)" class="space-y-4">
                <x-splade-checkboxes :options="$servers" name="servers" />
                <x-splade-submit />
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-app-layout>
