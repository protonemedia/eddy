@seoTitle(__('Backups'))

<x-splade-event private channel="teams.{{ auth()->user()->currentTeam->id }}" listen="BackupUpdated, BackupDeleted" />

<x-server-layout :$server>
    <x-slot:title>
        {{ __('Backups') }}
    </x-slot>

    <x-slot:description>
        {{ __('Manage your Backups.') }}
    </x-slot>

    @if ($backups->isNotEmpty())
        <x-slot:actions>
            <x-splade-button type="link" modal href="{{ route('servers.backups.create', $server) }}">
                {{ __('Add Backup') }}
            </x-splade-button>
        </x-slot>
    @endif

    <x-splade-table :for="$backups">
        <x-splade-cell status>
            @if (! $item->installed_at)
                <x-installation-status :installable="$item" />
            @elseif ($item->latestJob)
                {{ __('Latest job') }}: {{ $item->latestJob->status->name }}
            @else
                {{ __('No jobs yet') }}
            @endif
        </x-splade-cell>

        <x-slot:empty-state>
            <x-empty-state modal :href="route('servers.backups.create', $server)">
                {{ __('Add Backup') }}
            </x-empty-state>
        </x-slot>
    </x-splade-table>
</x-server-layout>
