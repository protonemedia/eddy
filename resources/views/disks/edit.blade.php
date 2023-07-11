@seoTitle(__('Add Backup Disk'))

<x-app-layout>
    <x-action-section>
        <x-slot:title>
            {{ __('Edit Backup Disk') }}
        </x-slot>

        <x-slot:description>
            {{ __('You can use a Backup Disk across multiple servers.') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form method="PATCH" :action="route('disks.update', $disk)" :default="$disk" class="space-y-4">
                <x-splade-input name="name" :label="__('Name')" />
                <x-splade-select name="filesystem_driver" :label="__('Filesystem Driver')" :options="$filesystemDrivers" disabled />

                @include('disks.configuration-fields', ['secretsRequired' => true])

                <div class="flex flex-row items-center justify-between">
                    <x-splade-submit />

                    <x-splade-link :confirm-danger="$disk->backups()->doesntExist()" method="DELETE" :href="route('disks.destroy', $disk)">
                        <x-splade-button danger :label="__('Delete Disk')" />
                    </x-splade-link>
                </div>
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-app-layout>
