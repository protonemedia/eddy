@seoTitle(__('Add Backup Disk'))

<x-app-layout>
    <x-action-section>
        <x-slot:title>
            {{ __('Add Backup Disk') }}
        </x-slot>

        <x-slot:description>
            {{ __('You can use a Backup Disk across multiple servers.') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form :action="route('disks.store')" class="space-y-4">
                <x-splade-input name="name" :label="__('Name')" />
                <x-splade-select name="filesystem_driver" :label="__('Filesystem Driver')" :options="$filesystemDrivers" />

                @include('disks.configuration-fields', ['secretsRequired' => false])

                <x-splade-submit />
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-app-layout>
