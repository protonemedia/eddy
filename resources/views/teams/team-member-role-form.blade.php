<x-splade-modal class="!p-0" :close-button="false">
    <x-splade-form method="put" :action="route('team-members.update', [$team, $user])" :default="['role' => $user->membership->role]">
        <x-dialog-modal>
            <x-slot:title>
                {{ __('Manage Role') }}
            </x-slot>

            <x-slot:content>
                <x-splade-group name="role">
                    <div class="relative z-0 mt-1 border border-gray-200 rounded-lg cursor-pointer">
                        @foreach($availableRoles as $role)
                            <button
                                type="button"
                                class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                                :class="{'border-t border-gray-200 focus:border-none rounded-t-none': @json(!$loop->first), 'rounded-b-none': @json(!$loop->last)}"
                                @click='form.role = @json($role->key)'
                            >
                                <div :class='{"opacity-50": form.role && form.role != @json($role->key)}'>
                                    <!-- Role Name -->
                                    <div class="flex items-center">
                                        <div class="text-sm text-gray-600" :class='{"font-semibold": form.role == @json($role->key)}'>
                                            {{ $role->name }}
                                        </div>

                                        <svg v-if='form.role == @json($role->key)' class="ml-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>

                                    <!-- Role Description -->
                                    <div class="mt-2 text-xs text-gray-600 text-left">
                                        {{ $role->description }}
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </x-splade-group>
            </x-slot>

            <x-slot:footer>
                <x-splade-button type="button" secondary :label="__('Cancel')" @click="modal.close" />
                <x-splade-submit :label="__('Save')" class="ml-3" />
            </x-slot>
        </x-dialog-modal>
    </x-splade-form>
</x-splade-modal>