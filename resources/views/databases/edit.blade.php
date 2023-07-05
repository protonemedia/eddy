<x-server-layout :$server :title="__('Database')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __("Database on server ':server'.", ['server' => $server->name]) }}
        </x-slot>

        <x-slot:content>
            <x-splade-form :default="$database" class="space-y-4">
                <x-splade-input name="name" :label="__('Name')" disabled />

                <div class="flex flex-row items-center justify-between">
                    <x-splade-link confirm-danger method="DELETE" :href="route('servers.databases.destroy', [$server, $database])">
                        <x-splade-button danger :label="__('Delete Database')" />
                    </x-splade-link>
                </div>
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-server-layout>
