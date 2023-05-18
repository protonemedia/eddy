@seoTitle(__('SSH Keys'))

<x-app-layout>
    <x-slot:header>
        {{ __('SSH Keys') }}
    </x-slot>

    <x-slot:description>
        {{ __('Manage your SSH Keys.') }}
    </x-slot>

    @if($sshKeys->isNotEmpty())
        <x-slot:actions>
            <x-splade-button type="link" modal href="{{ route('ssh-keys.create') }}">
                {{ __('Add SSH Key') }}
            </x-splade-button>
        </x-slot>
    @endif

    <x-splade-table :for="$sshKeys">
        <x-splade-cell actions use="$teamHasServer">
            <x-splade-button-with-dropdown class="max-w-fit" inline teleport>
                <x-slot:button> {{ __('Actions...') }} </x-slot:button>

                <ul class="divide-y divide-gray-200 text-sm text-gray-700">

                    @if($teamHasServer)
                        <Link href="{{ route('ssh-keys.servers.add-form', $item) }}" modal class="px-4 py-2 flex items-center justify-between hover:bg-gray-100 hover:text-gray-900 rounded-t-md">
                            {{ __('Add To Servers') }}
                        </Link>

                        <Link href="{{ route('ssh-keys.servers.remove-form', $item) }}" modal class="px-4 py-2 flex items-center justify-between hover:bg-gray-100 hover:text-gray-900">
                            {{ __('Remove From Servers') }}
                        </Link>
                    @endif

                    <Link href="{{ route('ssh-keys.destroy', $item) }}" method="DELETE" confirm-danger class="px-4 py-2 flex items-center justify-between hover:bg-gray-100 hover:text-gray-900 rounded-b-md">
                        {{ __('Delete Key') }}
                    </Link>

                    @if($teamHasServer)
                        <Link href="{{ route('ssh-keys.destroy', [$item, 'remove-from-servers' => 1]) }}" method="DELETE" confirm-danger class="px-4 py-2 flex items-center justify-between hover:bg-gray-100 hover:text-gray-900 rounded-b-md">
                            {{ __('Delete Key and Remove From Servers') }}
                        </Link>
                    @endif
                </ul>
            </x-splade-button-with-dropdown>
        </x-splade-cell>

        <x-slot:empty-state>
            <x-empty-state modal :href="route('ssh-keys.create')">
                {{ __('Add SSH Key') }}
            </x-empty-state>
        </x-slot>
    </x-splade-table>
</x-app-layout>