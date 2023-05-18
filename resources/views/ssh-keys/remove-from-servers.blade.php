@seoTitle(__('Remove SSH Key'))

<x-app-layout>
    <x-action-section>
        <x-slot:title>
            {{ __('Remove SSH Key from Servers') }}
        </x-slot>

        <x-slot:description>
            {{ __('Select servers to remove SSH Key from.') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form :action="route('ssh-keys.servers.remove', $sshKey)" class="space-y-4">
                <x-splade-checkboxes :options="$servers" name="servers"  />
                <x-splade-submit />
            </x-splade-form>
        </x-slot>
    </x-action>
</x-app-layout>