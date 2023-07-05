<x-server-layout :$server :title="__('Edit Daemon')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __("Edit Daemon on server ':server'.", ['server' => $server->name]) }}
        </x-slot>

        <x-slot:content>
            <x-splade-form method="PATCH" :action="route('servers.daemons.update', [$server, $daemon])" :default="$daemon" class="space-y-4">
                <x-splade-input name="command" :label="__('Command')" placeholder="php8.2 artisan horizon" autofocus />
                <x-splade-input name="directory" :label="__('Directory (optional)')" placeholder="/home/eddy/site.com/current" />

                <div class="grid grid-cols-2 gap-4">
                    <x-splade-input name="user" :label="__('User')" />
                    <x-splade-input name="processes" :label="__('Processes')" />
                    <x-splade-input name="stop_wait_seconds" :label="__('Stop Wait Seconds')" />
                    <x-splade-select name="stop_signal" :label="__('Stop Signal')" :options="$signals" />
                </div>

                <div class="flex flex-row items-center justify-between">
                    <x-splade-submit :label="__('Deploy')" />

                    <x-splade-link confirm-danger method="DELETE" :href="route('servers.daemons.destroy', [$server, $daemon])">
                        <x-splade-button danger :label="__('Delete Daemon')" />
                    </x-splade-link>
                </div>
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-server-layout>
