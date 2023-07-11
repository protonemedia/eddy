@seoTitle(__('Backup Disks'))

<x-app-layout>
    <x-slot:header>
        {{ __('Backup Disks') }}
    </x-slot>

    <x-slot:description>
        {{ __('Manage your Backup Disks.') }}
    </x-slot>

    @if ($disks->isNotEmpty())
        <x-slot:actions>
            <x-splade-button type="link" modal href="{{ route('disks.create') }}">
                {{ __('Add Backup Disk') }}
            </x-splade-button>
        </x-slot>
    @endif

    <x-splade-table :for="$disks">
        <x-slot:empty-state>
            <x-empty-state modal :href="route('disks.create')">
                {{ __('Add Backup Disk') }}
            </x-empty-state>
        </x-slot>
    </x-splade-table>
</x-app-layout>
