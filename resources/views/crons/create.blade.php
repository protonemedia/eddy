<x-server-layout :$server :title="__('Add Cron')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __("Add Cron on server ':server'.", ['server' => $server->name]) }}
        </x-slot>

        <x-slot:content>
            <x-splade-form :action="route('servers.crons.store', $server)" :default="[
                'user' => $server->username
            ]" class="space-y-4">
                <x-splade-input name="command" :label="__('Command')" placeholder="php8.2 /home/eddy/site.com/current/artisan schedule:run" autofocus />
                <x-splade-input name="user" :label="__('User')" />
                <x-splade-radios name="frequency" :label="__('Frequency')" :options="$frequencies" />
                <x-splade-input v-if="form.frequency == 'custom'" name="custom_expression" :label="__('Expression')" />
                <x-splade-submit :label="__('Deploy')" />
            </x-splade-form>
        </x-slot>
    </x-action>
</x-server-layout>