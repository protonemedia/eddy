<x-server-layout :$server :title="__('Add Firewall Rule')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __("Add Firewall Rule on server ':server'.", ['server' => $server->name]) }}
        </x-slot>

        <x-slot:content>
            <x-splade-form
                :action="route('servers.firewall-rules.store', $server)"
                :default="
                    [
                        'user' => $server->username,
                    ]
                "
                class="space-y-4"
            >
                <x-splade-input name="name" :label="__('Name')" />
                <x-splade-radios name="action" :label="__('Action')" :options="$actions" inline />

                <div class="grid grid-cols-2 gap-4">
                    <x-splade-input name="port" :label="__('Port')" />
                    <x-splade-input name="from_ipv4" :label="__('From IP (optional)')" />
                </div>

                <x-splade-submit :label="__('Deploy')" />
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-server-layout>
