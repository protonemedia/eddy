<x-server-layout :$server :title="__('Add Daemon')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __("Add Daemon on server ':server'.", ['server' => $server->name]) }}
        </x-slot>

        <x-slot:content>
            <x-splade-form :action="route('servers.daemons.store', $server)" :default="[
                'user' => $server->username,
                'processes' => 1,
                'stop_wait_seconds' => 10,
                'stop_signal' => 'TERM',
            ]" class="space-y-4">
                <x-splade-input name="command" :label="__('Command')" placeholder="php8.2 artisan horizon" autofocus />
                <x-splade-input name="directory" :label="__('Directory (optional)')" placeholder="/home/eddy/site.com/current" />

                <div class="grid grid-cols-2 gap-4">
                    <x-splade-input name="user" :label="__('User')" />
                    <x-splade-input name="processes" :label="__('Processes')" />
                    <x-splade-input name="stop_wait_seconds" :label="__('Stop Wait Seconds')" />
                    <x-splade-select name="stop_signal" :label="__('Stop Signal')" :options="$signals" />
                </div>
                <x-splade-submit :label="__('Deploy')" />
            </x-splade-form>
        </x-slot>
    </x-action>
</x-server-layout>