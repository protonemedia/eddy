@component('mail::message')
@if ($isFinished)
{{ __('Your backup job has run successfully.') }}
@else
{{ __('Your backup job has failed.') }}
@endif

@component('mail::panel')
{{ __('Backup') }}: `{{ $backup->name }}`

{{ __('From Server') }}: `{{ $server->name }}`

{{ __('To Destination') }}: `{{ $disk->name }}`

{{ __('Started At') }}: `{{ $backupJob->created_at->format('Y-m-d H:i:s') }}`

{{ __('Backup Job Size') }}: `{{ $backupJob->size_in_mb }} MB`

{{ __('Total Backup Size') }}: `{{ $backup->size_in_mb }} MB`
@if (! $isFinished)

{{ __('Error') }}: `{{ $backupJob->error }}`
@endif
@endcomponent

@component('mail::button', ['url' => route('servers.backups.show', [$server, $backup])])
{{ __('View Backup') }}
@endcomponent
@endcomponent
