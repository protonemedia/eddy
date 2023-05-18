<x-action-section>
    <x-slot:title>
        {{ __('Pending Team Invitations') }}
    </x-slot>

    <x-slot:description>
        {{ __('These people have been invited to your team and have been sent an invitation email. They may join the team by accepting the email invitation.') }}
    </x-slot>

    <!-- Pending Team Member Invitation List -->
    <x-slot:content>
        <div class="space-y-6">
            @foreach($team->teamInvitations as $invitation)
                <div class="flex items-center justify-between">
                    <div class="text-gray-600">
                        {{ $invitation->email }}
                    </div>

                    @if($permissions['canRemoveTeamMembers'])
                        <div class="flex items-center">
                            <!-- Cancel Team Invitation -->
                            <x-splade-form
                                method="delete"
                                :action="route('team-invitations.destroy', $invitation)"
                            >
                                <button type="submit" class="cursor-pointer ml-6 text-sm text-red-500 focus:outline-none">
                                    {{ __('Cancel') }}
                                </button>
                            </x-splade-form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-slot>
</x-action-section>