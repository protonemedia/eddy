<x-splade-form
    method="put"
    :action="route('user-profile-information.update')"
    :default="auth()->user()"
    stay
    @success="$splade.emit('profile-information-updated')"
>
    <x-form-section dusk="update-profile-information-form">
        <x-slot:title>
            {{ __('Profile Information') }}
        </x-slot>

        <x-slot:description>
            {{ __('Update your account\'s profile information and email address.') }}
        </x-slot>

        <x-slot:form>
            <!-- Profile Photo -->
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <div class="col-span-6 sm:col-span-4">
                    <span class="mb-1 block font-sans text-gray-700">{{ __('Photo') }}</span>

                    <!-- Current Profile Photo -->
                    <div v-show="!form.photo" class="mt-2">
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-full object-cover" />
                    </div>

                    <!-- New Profile Photo Preview -->
                    <div v-show="form.photo" class="mt-2">
                        <span
                            class="block h-20 w-20 rounded-full bg-cover bg-center bg-no-repeat"
                            :style="'background-image: url(\'' + form.$fileAsUrl('photo') + '\');'"
                        />
                    </div>

                    <!-- Profile Photo File Input -->
                    <div class="mt-2 flex space-x-2">
                        <x-splade-file name="photo" :show-filename="false">
                            {{ __('Select A New Photo') }}
                        </x-splade-file>

                        <x-splade-rehydrate on="profile-information-updated">
                            @if (auth()->user()->profile_photo_path)
                                <x-splade-link
                                    method="delete"
                                    :href="route('current-user-photo.destroy')"
                                    class="relative inline-block cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:border-indigo-300 focus:outline-none focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    {{ __('Remove Photo') }}
                                </x-splade-link>
                            @endif
                        </x-splade-rehydrate>
                    </div>
                </div>
            @endif

            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <x-splade-input id="name" name="name" :label="__('Name')" autocomplete="name" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <x-splade-input id="email" name="email" type="email" :label="__('Email')" autocomplete="name" />
                <div id="verify-email" />
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

@if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! auth()->user()->hasVerifiedEmail())
    {{-- This section over here is teleported so we don't have a form in a form. --}}
    <x-splade-teleport to="#verify-email">
        <x-splade-form :action="route('verification.send')" stay>
            <p v-if="!form.wasSuccessful" class="mt-2 text-sm">
                {{ __('Your email address is unverified.') }}

                <button
                    type="submit"
                    class="inline rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    {{ __('Click here to re-send the verification email.') }}
                </button>
            </p>

            <div v-if="form.wasSuccessful" class="mt-2 text-sm font-medium text-green-600">
                {{ __('A new verification link has been sent to your email address.') }}
            </div>
        </x-splade-form>
    </x-splade-teleport>
@endif
