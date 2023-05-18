<x-splade-event private channel="teams.{{ auth()->user()->currentTeam->id }}" listen="DaemonUpdated, DaemonDeleted" />

<x-server-layout :$server>
    <x-slot:title>
        {{ __('Daemons') }}
    </x-slot>

    <x-slot:description>
        {{ __("Manage the daemons on server ':server'.", ['server' => $server->name]) }}
    </x-slot>

    <x-slot:actions>
        @if($daemons->isNotEmpty())
            <x-splade-button type="link" modal href="{{ route('servers.daemons.create', $server) }}">
                {{ __('Add Daemon') }}
            </x-splade-button>
        @endif
    </x-slot>

    <x-splade-table :for="$daemons">
        <x-splade-cell status>
            <x-installation-status :installable="$item" />
        </x-splade-cell>

        <x-slot:empty-state>
            <x-empty-state modal :href="route('servers.daemons.create', $server)" icon="heroicon-o-document-plus">
                {{ __('Add Daemon') }}
            </x-empty-state>
        </x-slot>
    </x-splade-table>
</x-server-layout>