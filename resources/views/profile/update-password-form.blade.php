<x-splade-form method="put" :action="route('user-password.update')" stay>
    <x-form-section>
        <x-slot:title>
            {{ __('Update Password') }}
        </x-slot>

        <x-slot:description>
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </x-slot>

        <x-slot:form>
            <div class="col-span-6 sm:col-span-4">
                <x-splade-input id="current_password" type="password" name="current_password" :label="__('Current Password')" autocomplete="current-password" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <x-splade-input id="password" type="password" name="password" :label="__('New Password')" autocomplete="new-password" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <x-splade-input id="password_confirmation" type="password" name="password_confirmation" :label="__('Confirm Password')" autocomplete="new-password" />
            </div>
        </x-slot>

        <x-slot:actions>
            <x-action-message v-if="form.recentlySuccessful" class="mr-3">
                {{ __('Saved.') }}
            </x-action-message>

            <x-splade-submit :label="__('Save')" />
        </x-slot>
    </x-form-section>
</x-splade-form>