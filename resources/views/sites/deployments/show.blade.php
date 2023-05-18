<x-splade-event private channel="teams.{{ auth()->user()->currentTeam->id }}" listen="DeploymentUpdated" preserve-scroll />

<x-site-layout :site="$site" :title="__('Deployment at :at', ['at' => $deployment->created_at])">
    <x-slot:actions>
        <x-splade-button type="link" :href="route('servers.sites.deployments.index', [$server, $site])" class="flex items-center justify-center">
            @svg('heroicon-s-arrow-left-circle', 'h-5 w-5 -ml-1 mr-2')
            <span class="text-center">{{ __('All Deployments') }}</span>
        </x-splade-button>
    </x-slot>

    <x-action-section in-sidebar-layout modal-max-width="4xl" :modal-close-explicitly="false">
        <x-slot:title>
            <div class="flex flex-row items-center">
                {{ __('Output Log') }}

                @if($deployment->status == \App\Models\DeploymentStatus::Pending)
                    @svg('heroicon-s-cog-6-tooth', 'h-5 w-5 text-gray-400 ml-2 animate-spin')
                @endif
            </div>
        </x-slot>

        <x-slot:content>
            <div class="overflow-x-auto max-w-full">
                <x-ansicolor class="text-sm">{!! $deployment->task?->output ?: '...' !!}</x-ansicolor>
            </div>
        </x-slot:content>
    </x-action-section>
</x-site-layout>
