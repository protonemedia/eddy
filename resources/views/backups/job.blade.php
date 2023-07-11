<x-splade-modal>
    @if ($isPendingOrRunning)
        {{ __('Backup is running...') }}
    @elseif ($isFinished)
        {{ __('Backup finished without errors.') }}
    @else
        {{ __('The following errors occurred during the backup:') }}
        <x-ansicolor class="whitespace-normal text-sm">{{ $backupJob->error }}</x-ansicolor>
    @endif
</x-splade-modal>
