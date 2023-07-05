@seoTitle(__('Create Team'))

<x-app-layout>
    <x-slot:header>
        {{ __('Create Team') }}
    </x-slot>

    <x-splade-form :action="route('teams.store')">
        <x-form-section>
            <x-slot:title>
                {{ __('Team Details') }}
            </x-slot>

            <x-slot:description>
                {{ __('Create a new team to collaborate with others on projects.') }}
            </x-slot>

            <x-slot:form>
                <div class="col-span-6">
                    <x-splade-group :label="__('Team Owner')">
                        <div class="mt-2 flex items-center">
                            <img class="h-12 w-12 rounded-full object-cover" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" />

                            <div class="ml-4 leading-tight">
                                <div class="text-gray-900">{{ auth()->user()->name }}</div>
                                <div class="text-sm text-gray-700">
                                    {{ auth()->user()->email }}
                                </div>
                            </div>
                        </div>
                    </x-splade-group>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <x-splade-input id="name" name="name" :label="__('Team Name')" autofocus />
                </div>
            </x-slot>

            <x-slot:actions>
                <x-splade-submit :label="__('Create')" />
            </x-slot>
        </x-form-section>
    </x-splade-form>
</x-app-layout>
