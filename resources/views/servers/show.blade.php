<x-server-layout :server="$server" :title="__('Server Overview')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __('Server Overview') }}
        </x-slot>

        <x-slot:content>
            <dl class="sm:divide-y sm:divide-gray-200">
                <x-description-list-item :label="__('Name')">
                    <span>{{ $server->name }}</span>
                </x-description-list-item>

                <x-description-list-item :label="__('IP Address')">
                    <span>{{ $server->public_ipv4 }}</span>
                    <x-clipboard class="ml-1 h-5 w-5">
                        {{ $server->public_ipv4 }}
                    </x-clipboard>
                </x-description-list-item>

                <x-description-list-item :label="__('Provider')">
                    <span>{{ $server->provider->getDisplayName() }}</span>
                </x-description-list-item>

                @if ($server->provider_id)
                    <x-description-list-item :label="__('Provider ID')">
                        <span>{{ $server->provider_id }}</span>
                    </x-description-list-item>
                @endif

                <x-description-list-item :label="__('Installed Software')">
                    <ul>
                        @foreach ($server->installed_software as $software)
                            <li>
                                {{ \App\Server\Software::from($software)->getDisplayName() }}
                            </li>
                        @endforeach
                    </ul>
                </x-description-list-item>
            </dl>
        </x-slot>
    </x-action-section>

    <x-action-section in-sidebar-layout class="mt-8">
        <x-slot:title>
            {{ __('Delete Server') }}
        </x-slot>

        <x-slot:description>
            {{ __('Deleting a server will remove all settings, sites and deployments associated with it.') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form confirm-danger method="DELETE" :action="route('servers.destroy', $server)">
                <x-splade-submit danger :label="__('Delete Server')" />
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-server-layout>
