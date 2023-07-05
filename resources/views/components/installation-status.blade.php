<div class="flex flex-row items-center">
    @unless ($installable->installation_failed_at || $installable->uninstallation_failed_at)
        @if (! $installable->installed_at || $installable->uninstallation_requested_at)
            @svg('heroicon-s-cog-6-tooth', 'h-5 w-5 text-gray-400 mr-2 animate-spin')
        @endif
    @endunless

    @if ($installable->installation_failed_at)
        {{ __('Installation failed') }}
    @elseif ($installable->uninstallation_failed_at)
        {{ __('Uninstallation failed') }}
    @elseif ($installable->uninstallation_requested_at)
        {{ __('Uninstalling') }}...
    @elseif ($installable->installed_at)
        {{ __('Installed') }}
    @else
        {{ __('Installing') }}...
    @endif
</div>
