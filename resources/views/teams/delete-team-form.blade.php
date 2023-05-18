<x-action-section>
    <x-slot:title>
        {{ __('Delete Team') }}
    </x-slot>

    <x-slot:description>
        {{ __('Permanently delete this team.') }}
    </x-slot>

    <x-slot:content>
        <div class="max-w-xl text-sm text-gray-600">
            {{ __('Once a team is deleted, all of its resources and data will be permanently deleted. Before deleting this team, please download any data or information regarding this team that you wish to retain.') }}
        </div>

        <div class="mt-5">
            <x-splade-form
                method="delete"
                :action="route('teams.destroy', $team)"
                :confirm="__('Delete Team')"
                :confirm-text="__('Are you sure you want to delete this team? Once a team is deleted, all of its resources and data will be permanently deleted.')"
                :confirm-button="__('Delete Team')"
            >
                <x-splade-submit :label="__('Delete Team')" />
            </x-splade-form>
        </div>
    </x-slot>
</x-action-section>
