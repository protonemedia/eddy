@seoTitle(__('Secure Area'))

<x-authentication-card>
    <x-slot:logo>
        <x-authentication-card-logo />
    </x-slot>

    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <x-splade-form class="space-y-4" :action="route('password.confirm')">
        <x-splade-input id="password" name="password" type="password" :label="__('Password')" required autocomplete="current-password" autofocus />

        <div class="flex items-center justify-end mt-4">
            <x-splade-submit :label="__('Confirm')" />
        </div>
    </x-splade-form>
</x-authentication-card>
