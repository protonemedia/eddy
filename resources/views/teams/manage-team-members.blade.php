<x-action-section>
    <x-slot:title>
        {{ __('Team Members') }}
    </x-slot>

    <x-slot:description>
        {{ __('All of the people that are part of this team.') }}
    </x-slot>

    <!-- Team Member List -->
    <x-slot:content>
        <div class="space-y-6">
            @foreach($team->users as $user)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img class="w-8 h-8 rounded-full" src="{{ $user->profile_photo_url }}" :alt="@js($user->name)">
                        <div class="ml-4" v-text="@js($user->name)" />
                    </div>

                    <div class="flex items-center">
                        <!-- Manage Team Member Role -->
                        @if($permissions['canAddTeamMembers'] && !empty($availablePermissions))
                            <Link modal href="{{ route('team-members.edit', [$team, $user]) }}" class="ml-2 text-sm text-gray-400 underline">
                                {{ collect($availableRoles)->firstWhere('key', $user->membership->role)?->name }}
                            </Link>
                        @elseif(!empty($availablePermissions))
                            <div class="ml-2 text-sm text-gray-400">
                                {{ collect($availableRoles)->firstWhere('key', $user->membership->role)?->name }}
                            </div>
                        @endif

                        @if(auth()->user()->is($user))
                            <x-splade-form
                                method="delete"
                                :action="route('team-members.destroy', [$team, $user])"
                                :confirm="__('Leave Team')"
                                :confirm-text="__('Are you sure you would like to leave this team?')"
                                :confirm-button="__('Leave')"
                            >
                                <button type="submit" class="cursor-pointer ml-6 text-sm text-red-500">
                                    {{ __('Leave') }}
                                </button>
                            </x-splade-form>
                        @elseif($permissions['canRemoveTeamMembers'])
                            <x-splade-form
                                method="delete"
                                :action="route('team-members.destroy', [$team, $user])"
                                :confirm-danger="__('Remove Team Member')"
                                :confirm-text="__('Are you sure you would like to remove this person from the team?')"
                                :confirm-button="__('Remove')"
                            >
                                <button type="submit" class="cursor-pointer ml-6 text-sm text-red-500">
                                    {{ __('Remove') }}
                                </button>
                            </x-splade-form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-slot>
</x-action-section>