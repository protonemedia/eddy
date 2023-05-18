<x-server-layout :$server>
    <x-slot:title>
        {{ __('Sites') }}
    </x-slot>

    <x-slot:description>
        {{ __("Manage the sites on server ':server'.", ['server' => $server->name]) }}
    </x-slot>

    <x-slot:actions>
        <x-splade-button type="link" modal href="{{ route('servers.sites.create', $server) }}">
            {{ __('New Site') }}
        </x-splade-button>
    </x-slot>

    @if($sites->isNotEmpty())
        <x-slot:actions>
            <x-splade-button type="link" modal href="{{ route('servers.sites.create', $server) }}">
                {{ __('New Site') }}
            </x-splade-button>
        </x-slot>
    @endif

    <x-splade-table :for="$sites">
        <x-slot:empty-state>
            <x-empty-state modal :href="route('servers.sites.create', $server)" icon="heroicon-o-document-plus">
                {{ __('New Site') }}
            </x-empty-state>
        </x-slot>
    </x-splade-table>
</x-server-layout>