@seoTitle(__('Profile'))

<x-app-layout>
    <x-slot:header>
        {{ __('Profile') }}
    </x-slot>

    @if(Laravel\Fortify\Features::canUpdateProfileInformation())
        @include('profile.update-profile-information-form')

        <x-section-border />
    @endif

    @if(Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
        <div class="mt-10 sm:mt-0" dusk="update-password-form">
            @include('profile.update-password-form')
        </div>

        <x-section-border />
    @endif

    @if(Laravel\Fortify\Features::canManageTwoFactorAuthentication())
        <div class="mt-10 sm:mt-0" dusk="two-factor-authentication-form">
            @include('profile.two-factor-authentication-form')
        </div>

        <x-section-border />
    @endif

    <div class="mt-10 sm:mt-0" dusk="logout-other-browser-sessions-form">
        @include('profile.logout-other-browser-sessions-form')
    </div>

    @if(Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
        <x-section-border />

        <div class="mt-10 sm:mt-0" dusk="delete-user-form">
            @include('profile.delete-user-form')
        </div>
    @endif
</x-app-layout>
