<x-server-layout :$server :title="__('Edit Cron')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __("Edit Firewall Rule on server ':server'.", ['server' => $server->name]) }}
        </x-slot>

        <x-slot:content>
            <x-splade-form
                method="PATCH"
                :action="route('servers.firewall-rules.update', [$server, $firewallRule])"
                :default="$firewallRule"
                class="space-y-4"
            >
                <x-splade-input name="name" :label="__('Name')" />

                <div class="flex flex-row items-center justify-between">
                    <x-splade-submit :label="__('Save')" />

                    <x-splade-link confirm-danger method="DELETE" :href="route('servers.firewall-rules.destroy', [$server, $firewallRule])">
                        <x-splade-button danger :label="__('Delete Firewall Rule')" />
                    </x-splade-link>
                </div>
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-server-layout>
