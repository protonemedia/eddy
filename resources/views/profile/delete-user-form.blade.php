<x-action-section>
    <x-slot:title>
        {{ __('Delete Account') }}
    </x-slot>

    <x-slot:description>
        {{ __('Permanently delete your account.') }}
    </x-slot>

    <x-slot:content>
        <div class="max-w-xl text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </div>

        <x-splade-form
            class="mt-5"
            method="delete"
            :action="route('current-user.destroy')"
            :confirm-danger="__('Delete Account')"
            :confirm-text="__('Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.')"
            :confirm-button="__('Delete Account')"
            require-password
        >
            <x-splade-submit danger :label="__('Delete Account')" />
        </x-splade-form>
    </x-slot>
</x-action-section>
