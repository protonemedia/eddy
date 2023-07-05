@seoTitle($site->address.(isset($title) ? " - $title" : ''))

<x-sidebar-layout :$navigationItems :$href>
    <x-slot:header>
        {{ $site->address }}
        @isset($title)
            - {{ $title }}
        @endisset
    </x-slot>

    <x-slot:description>
        <div class="flex flex-row items-center space-x-2">
            <Link href="{{ route('servers.show', $server) }}" class="underline">
                {{ $server->name }}
            </Link>

            <div class="h-1 w-1 rounded-full bg-gray-400" />

            <span>{{ $site->php_version->getDisplayName() }}</span>

            <div class="h-1 w-1 rounded-full bg-gray-400" />

            <p class="flex flex-row items-center">
                <span>{{ $server->public_ipv4 }}</span>
                <x-clipboard class="ml-1 h-4 w-4">{{ $server->public_ipv4 }}</x-clipboard>
            </p>
        </div>
    </x-slot>

    <x-slot:actions>
        <x-splade-button
            :confirm="$site->isDeploying() ? false : __('Are you sure you want to start a new deployment?')"
            :method="$site->isDeploying() ? 'GET' : 'POST'"
            type="link"
            :href="$site->isDeploying() ? route('servers.sites.deployments.show', [$server, $site, $site->latestDeployment]) : route('servers.sites.deployments.store', [$server, $site])"
            class="flex items-center justify-center"
            dusk="deploy-site"
        >
            @if ($site->isDeploying())
                @svg('heroicon-o-cog-6-tooth', 'h-5 w-5 -ml-1 mr-2 animate-spin')
                <span>{{ __('Deploying...') }}</span>
            @else
                @svg('heroicon-o-arrow-up-on-square-stack', 'h-5 w-5 -ml-1 mr-2')
                <span>{{ __('Deploy') }}</span>
            @endif
        </x-splade-button>
    </x-slot>

    {{ $slot }}
</x-sidebar-layout>
