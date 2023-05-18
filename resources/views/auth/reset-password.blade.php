@seoTitle(__('Reset Password'))

<x-authentication-card>
    <x-slot:logo>
        <x-authentication-card-logo />
    </x-slot>

    <x-splade-form class="space-y-4" :action="route('password.update')" :default="['email' => request()->input('email'), 'token' => request()->route('token')]">
        <x-splade-input id="email" name="email" type="email" :label="__('Email')" required autofocus />
        <x-splade-input id="password" name="password" type="password" :label="__('Password')" required autocomplete="new-password" />
        <x-splade-input id="password_confirmation" name="password_confirmation" type="password" :label="__('Confirm Password')" required autocomplete="new-password" />

        <div class="flex items-center justify-end mt-4">
            <x-splade-submit :label="__('Reset Password')" />
        </div>
    </x-splade-form>
</x-authentication-card>
