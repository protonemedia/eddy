<x-splade-event private channel="teams.{{ auth()->user()->currentTeam->id }}" listen="FirewallRuleUpdated, FirewallRuleDeleted" />

<x-server-layout :$server>
    <x-slot:title>
        {{ __('Firewall Rules') }}
    </x-slot>

    <x-slot:description>
        {{ __('Manage your Firewall Rules.') }}
    </x-slot>

    @if($firewallRules->isNotEmpty())
        <x-slot:actions>
            <x-splade-button type="link" modal href="{{ route('servers.firewall-rules.create', $server) }}">
                {{ __('Add Firewall Rule') }}
            </x-splade-button>
        </x-slot>
    @endif

    <x-splade-table :for="$firewallRules">
        <x-splade-cell status>
            <x-installation-status :installable="$item" />
        </x-splade-cell>

        <x-slot:empty-state>
            <x-empty-state modal :href="route('servers.firewall-rules.create', $server)">
                {{ __('Add Firewall Rule') }}
            </x-empty-state>
        </x-slot>
    </x-splade-table>
</x-server-layout>