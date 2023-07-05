<x-server-layout :server="$server" :title="__('Software')">
    <x-splade-table :for="$software">
        <x-splade-cell actions use="$server">
            <div class="flex space-x-4">
                @if ($item['hasUpdateAlternativesTask'])
                    <x-splade-form :action="route('servers.software.default', [$server, $item['id']])" confirm>
                        <x-splade-submit :label="__('Make CLI default')" />
                    </x-splade-form>
                @endif

                @if ($item['hasRestartTask'])
                    <x-splade-form :action="route('servers.software.restart', [$server, $item['id']])" confirm-danger>
                        <x-splade-submit danger :label="__('Restart')" />
                    </x-splade-form>
                @endif
            </div>
        </x-splade-cell>
    </x-splade-table>
</x-server-layout>
