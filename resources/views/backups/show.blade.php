@seoTitle($backup->name)

<x-splade-event private channel="backups.{{ $backup->id }}" listen="BackupUpdated" />

<x-server-layout :$server>
    <x-slot:title>
        {{ $backup->name }}
    </x-slot>

    <x-slot:description>
        {{ __('Manage your Backups.') }}
    </x-slot>

    @if ($backup->createdByUser()->is(auth()->user()))
        <x-slot:actions>
            <x-splade-button type="link" modal href="{{ route('servers.backups.edit', [$server, $backup]) }}">
                {{ __('Edit Backup') }}
            </x-splade-button>
        </x-slot>
    @endif

    <h1 class="pl-4 text-lg font-medium text-gray-900 sm:pl-0">
        {{ __('Backup: :name', ['name' => $backup->name]) }}
    </h1>

    <x-action-section class="mt-4" in-sidebar-layout>
        <x-slot:content>
            <dl class="sm:divide-y sm:divide-gray-200">
                <x-description-list-item :label="__('Destination')">
                    <span>{{ $backup->disk->name }}</span>
                </x-description-list-item>

                <x-description-list-item :label="__('Databases')">
                    <span>{{ $backup->databases->pluck('name')->implode(', ') }}</span>
                </x-description-list-item>

                <x-description-list-item :label="__('Files')">
                    <pre>{{ implode(PHP_EOL, $backup->include_files->toArray()) }}</pre>
                </x-description-list-item>

                <x-description-list-item :label="__('Frequency')">
                    <span>{{ $frequencies[$backup['cron_expression']] ?? $backup['cron_expression'] }}</span>
                </x-description-list-item>

                <x-description-list-item :label="__('Total Size')">
                    <span>{{ $backup->size_in_mb }} MB</span>
                </x-description-list-item>

                <x-description-list-item :label="__('Next run')">
                    <span>{{ $backup->next_run->diffForHumans() }}</span>
                </x-description-list-item>
            </dl>
        </x-slot>
    </x-action-section>

    <h1 class="mt-4 pl-4 text-lg font-medium text-gray-900 sm:pl-0">
        {{ __('Latest Jobs') }}
    </h1>

    <x-splade-table class="mt-4" :for="$jobs" :reset-button="false" pagination-scroll="head" />
</x-server-layout>
