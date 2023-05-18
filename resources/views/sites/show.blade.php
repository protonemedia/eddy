<x-splade-event private channel="teams.{{ auth()->user()->currentTeam->id }}" listen="SiteUpdated" preserve-scroll />

<x-site-layout :site="$site" :title="__('Site Overview')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __('Site Overview') }}
        </x-slot>

        <x-slot:content>
            <dl class="sm:divide-y sm:divide-gray-200">
                <x-description-list-item :label="__('Address')">
                    <span>{{ $site->address }}</span>

                    <a href="{{ $site->url }}" target="_blank" class="ml-1">
                        @svg('heroicon-s-arrow-right-circle', 'w-5 h-5 text-gray-700')
                    </a>
                </x-description-list-item>

                <x-description-list-item :label="__('Server')">
                    <Link class="underline" href="{{ route('servers.show', $server) }}">
                        {{ $server->name }}
                    </Link>
                </x-description-list-item>

                <x-description-list-item :label="__('Path')">
                    <span>{{ $site->path }}</span>
                    <x-clipboard class="ml-1 w-5 h-5">{{ $site->path }}</x-clipboard>
                </x-description-list-item>

                <x-description-list-item :label="__('PHP Version')">
                    {{ $site->php_version->getDisplayName() }}
                </x-description-list-item>

                <x-description-list-item :label="__('Type')">
                    {{ $site->type->getDisplayName() }}
            </x-description-list-item>

            <x-description-list-item :label="__('SSL')">
                {{ $site->tls_setting->getDisplayName() }}
            </x-description-list-item>

                @if($site->repository_url)
                    <x-description-list-item :label="__('Repository')">
                        {{ $site->repository_url }} ({{ $site->repository_branch }})
                    </x-description-list-item>
                @endif
            </dl>
        </x-slot:content>
    </x-action-section>

    <x-action-section in-sidebar-layout class='mt-8 '>
        <x-slot:title>
            {{ __('Deployment') }}
        </x-slot>

        <x-slot:content>
            <dl class="sm:divide-y sm:divide-gray-200">
                <x-description-list-item :label="__('Zero Downtime Deployment')">
                    {{ $site->zero_downtime_deployment ? __('Yes') : __('No') }}
                </x-description-list-item>

                @if($site->latestDeployment)
                    <x-description-list-item :label="__('Latest Deployment')">
                        <Link class="underline" href="{{ route('servers.sites.deployments.show', [$server, $site, $site->latestDeployment]) }}">
                            {{ $site->latestDeployment->updated_at }}
                        </Link>
                    </x-description-list-item>
                @endif

                <x-description-list-item :label="__('Deploy URL')">
                    <span class="break-all pr-4">
                        {{ route('site.deployWithToken', [$site, $site->deploy_token]) }}
                    </span>

                    <Link
                        dusk="refresh-deploy-token"
                        method="POST"
                        href="{{ route('servers.sites.refresh-deploy-token', [$server, $site]) }}"
                        confirm="{{ __('Are you sure you want to regenerate the deploy token?') }}"
                        confirm-text="{{ __('This will invalidate the current deploy token.') }}"
                        class="ml-1">
                        @svg('heroicon-o-arrow-path', 'w-5 h-5 text-gray-700')
                    </Link>
                </x-description-list-item>
            </dl>
        </x-slot:content>
    </x-action-section>

    <x-action-section in-sidebar-layout class='mt-8'>
        <x-slot:title>
            {{ __('Delete Site') }}
        </x-slot>

        <x-slot:description>
            {{ __('Deleting a site will remove all files associated with it. This action cannot be undone.') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form confirm-danger method="DELETE" :action="route('servers.sites.destroy', [$server, $site])">
                <x-splade-submit danger :label="__('Delete Site')" />
            </x-splade-form>
        </x-slot:content>
    </x-action-section>
</x-site-layout>
