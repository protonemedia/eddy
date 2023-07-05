@seoTitle($server->name)

<x-splade-event private channel="teams.{{ auth()->user()->currentTeam->id }}" listen="ServerUpdated, ServerDeleted" preserve-scroll />

@php
    $isNew = $server->status === \App\Infrastructure\Entities\ServerStatus::New;
    $isStarting = $server->status === \App\Infrastructure\Entities\ServerStatus::Starting;
    $isProvisioning = $server->status === \App\Infrastructure\Entities\ServerStatus::Provisioning;
@endphp

@if ($server->provider === \App\Provider::CustomServer && ! $isProvisioning)
    <x-splade-modal opened name="provision-script">
        <p>{{ __('Run this script as root on your server to start the provisioning process:') }}</p>

        <p class="relative mt-4 break-all pr-8 font-mono text-sm">
            {{ $server->provisionCommand() }}
            <x-clipboard class="absolute right-0 top-0 h-5 w-5">{{ $server->provisionCommand() }}</x-clipboard>
        </p>
    </x-splade-modal>
@endif

<x-app-layout>
    <x-action-section>
        <x-slot:title>
            {{ $server->name }}
        </x-slot>

        <x-slot:description>
            @if ($server->status === \App\Infrastructure\Entities\ServerStatus::Provisioning)
                {{ __('The server is currently being provisioned.') }}
            @elseif ($server->status === \App\Infrastructure\Entities\ServerStatus::Starting)
                {{ __('The server is created at the provider and is currently starting up.') }}
            @else
                {{ __('The server is currently being created at the provider.') }}
            @endif

            {{ __('This page will automatically refresh on updates.') }}

            <x-splade-form
                confirm-danger
                method="DELETE"
                :action="route('servers.destroy', $server)"
                :confirm-text="__('Deleting a server will remove all settings. We will delete it for you, but you might have to manually remove it from your provider.')"
                class="mt-4"
            >
                <x-splade-submit danger :label="__('Delete Server')" />
            </x-splade-form>

            @if ($server->provider === \App\Provider::CustomServer && ! $isProvisioning)
                <p class="mt-4">{{ __('Need to see the provisioning script again?') }}</p>

                <x-splade-button type="link" secondary href="#provision-script" class="mt-4 inline-flex">
                    {{ __('View Provisioning Script') }}
                </x-splade-button>
            @endif
        </x-slot>

        <x-slot:content>
            <ol role="list" class="space-y-6">
                <x-dynamic-component :component="$isNew ? 'step.current' : 'step.complete'">
                    {{ __('Create the server at the provider') }}
                </x-dynamic-component>

                <x-dynamic-component :component="$isStarting ? 'step.current' : ($isNew ? 'step.upcoming' : 'step.complete')">
                    {{ __('Wait for the server to start up') }}
                </x-dynamic-component>

                @php
                    $lastStepWasCompleted = $isProvisioning;
                    $completedSteps = $server->completed_provision_steps->toArray();
                @endphp

                @foreach (\App\Server\ProvisionStep::forFreshServer() as $step)
                    @php
                        $completed = in_array($step->value, $completedSteps);
                        $current = ! $completed && $lastStepWasCompleted;
                        $lastStepWasCompleted = $completed;
                    @endphp

                    <x-dynamic-component :component="$completed ? 'step.complete' : ($current ? 'step.current' : 'step.upcoming')">
                        {{ $step->getDescription() }}
                    </x-dynamic-component>
                @endforeach

                @php
                    $installedSoftware = $server->installed_software->toArray();
                @endphp

                @foreach (\App\Server\Software::defaultStack() as $software)
                    @php
                        $completed = in_array($software->value, $installedSoftware);
                        $current = ! $completed && $lastStepWasCompleted;
                        $lastStepWasCompleted = $completed;
                    @endphp

                    <x-dynamic-component :component="$completed ? 'step.complete' : ($current ? 'step.current' : 'step.upcoming')">
                        {{ __('Install :software', ['software' => $software->getDisplayName()]) }}
                    </x-dynamic-component>
                @endforeach
            </ol>
        </x-slot>
    </x-action-section>
</x-app-layout>
