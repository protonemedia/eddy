@seoTitle($server->name.(isset($title) ? " - $title" : ''))

<x-sidebar-layout :$navigationItems :$href>
    <x-slot:header>
        {{ $server->name }}
        @isset($title)
            - {{ $title }}
        @endisset
    </x-slot>

    <x-slot:description>
        <div class="flex flex-row items-center space-x-2">
            <span>{{ $server->provider_name }}</span>

            <div class="h-1 w-1 rounded-full bg-gray-400" />

            <p class="flex flex-row items-center">
                <span>{{ $server->public_ipv4 }}</span>
                <x-clipboard class="ml-1 h-4 w-4">{{ $server->public_ipv4 }}</x-clipboard>
            </p>
        </div>
    </x-slot>

    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot>
    @endisset

    {{ $slot }}
</x-sidebar-layout>
