<x-splade-form method="put" :action="route('teams.update', $team)" :default="$team" stay @success="$splade.emit('refresh-navigation-menu')">
    <x-form-section dusk="update-team-name-form">
        <x-slot:title>
            {{ __('Team Name') }}
        </x-slot>

        <x-slot:description>
            {{ __('The team\'s name and owner information.') }}
        </x-slot>

        <x-slot:form>
            <!-- Team Owner Information -->
            <div class="col-span-6">
                <x-splade-group :label="__('Team Owner')">
                    <div class="flex items-center mt-2">
                        <img class="w-12 h-12 rounded-full object-cover" src="{{ $team->owner->profile_photo_url }}" :alt="@js($team->owner->name)">

                        <div class="ml-4 leading-tight">
                            <div class="text-gray-900" v-text="@js($team->owner->name)" />
                            <div class="text-gray-700 text-sm">
                                {{ $team->owner->email }}
                            </div>
                        </div>
                    </div>
                </x-splade-group>
            </div>

            <!-- Team Name -->
            <div class="col-span-6 sm:col-span-4">
                <x-splade-input id="name" name="name" :label="__('Team Name')" :disabled="!$permissions['canUpdateTeam']" />
            </div>
        </x-slot>

        @if($permissions['canUpdateTeam'])
            <x-slot:actions>
                <x-action-message v-if="form.recentlySuccessful" class="mr-3">
                    {{ __('Saved.') }}
                </x-action-message>

                <x-splade-submit :label="__('Save')" />
            </x-slot>
        @endif
    </x-form-section>
</x-splade-form>
