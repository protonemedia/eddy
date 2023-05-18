<x-splade-event private channel="teams.{{ auth()->user()->currentTeam->id }}" listen="DatabaseUpdated, DatabaseDeleted, DatabaseUserUpdated, DatabaseUserDeleted" />

<x-server-layout :$server>
    <x-slot:title>
        {{ __('Databases') }}
    </x-slot>

    <x-slot:description>
        {{ __("Manage the databases on server ':server'.", ['server' => $server->name]) }}
    </x-slot>

    <x-slot:actions>
        @if($databases->isNotEmpty())
            <x-splade-button type="link" modal href="{{ route('servers.databases.create', $server) }}">
                {{ __('Add Database') }}
            </x-splade-button>
        @endif

        @if($users->isNotEmpty())
            <x-splade-button type="link" modal href="{{ route('servers.database-users.create', $server) }}" class="ml-2">
                {{ __('Add User') }}
            </x-splade-button>
        @endif
    </x-slot>

    <div dusk="databases">
        @unless($databases->isEmpty() && $users->isEmpty())
            <h1 class="text-base font-semibold leading-6 text-gray-900 ml-4 sm:ml-0 mb-4">{{ __('Databases') }}</h1>
        @endunless

        <x-splade-table dusk="databases" :for="$databases">
            <x-splade-cell status>
                <x-installation-status :installable="$item" />
            </x-splade-cell>

            <x-slot:empty-state>
                <x-empty-state modal :href="route('servers.databases.create', $server)" icon="heroicon-o-document-plus">
                    {{ __('Add Database') }}
                </x-empty-state>
            </x-slot>
        </x-splade-table>
    </div>

    @unless($databases->isEmpty() && $users->isEmpty())
        <div dusk="users">
            <h1 class="text-base font-semibold leading-6 text-gray-900 ml-4 sm:ml-0 mt-8">{{ __('Users') }}</h1>

            <x-splade-table :for="$users" class="mt-4">
                <x-splade-cell status>
                    <x-installation-status :installable="$item" />
                </x-splade-cell>

                <x-slot:empty-state>
                    <x-empty-state modal :href="route('servers.database-users.create', $server)" icon="heroicon-o-document-plus">
                        {{ __('Add User') }}
                    </x-empty-state>
                </x-slot>
            </x-splade-table>
        </div>
    @endunless
</x-server-layout>