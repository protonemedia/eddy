<x-splade-event private channel="teams.{{ auth()->user()->currentTeam->id }}" listen="CronUpdated, CronDeleted" />

<x-server-layout :$server>
    <x-slot:title>
        {{ __('Crons') }}
    </x-slot>

    <x-slot:description>
        {{ __("Manage the crons on server ':server'.", ['server' => $server->name]) }}
    </x-slot>

    <x-slot:actions>
        @if ($crons->isNotEmpty())
            <x-splade-button type="link" modal href="{{ route('servers.crons.create', $server) }}">
                {{ __('Add Cron') }}
            </x-splade-button>
        @endif
    </x-slot>

    <x-splade-table :for="$crons">
        <x-splade-cell status>
            <x-installation-status :installable="$item" />
        </x-splade-cell>

        <x-slot:empty-state>
            <x-empty-state modal :href="route('servers.crons.create', $server)" icon="heroicon-o-document-plus">
                {{ __('Add Cron') }}
            </x-empty-state>
        </x-slot>
    </x-splade-table>
</x-server-layout>
