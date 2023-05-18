@seoTitle(__('API Tokens'))

<x-app-layout>
    <x-slot:header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('API Tokens') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div>
                <!-- Generate API Token -->
                <x-splade-form :action="route('api-tokens.store')">
                    <x-form-section>
                        <x-slot:title>
                            {{ __('Create API Token') }}
                        </x-slot>

                        <x-slot:description>
                            {{ __('API tokens allow third-party services to authenticate with our application on your behalf.') }}
                        </x-slot>

                        <x-slot:form>
                            <!-- Token Name -->
                            <div class="col-span-6 sm:col-span-4">
                                <x-splade-input name="name" autofocus label="Name" />
                            </div>

                            <!-- Token Permissions -->
                            @if(count($availablePermissions) > 0)
                                <div class="col-span-6">
                                    <x-splade-checkboxes
                                        name="permissions"
                                        label="Permissions"
                                        class="grid grid-cols-1 md:grid-cols-2 gap-1"
                                        :options="array_combine($availablePermissions, $availablePermissions)"
                                    />
                                </div>
                            @endif
                        </x-slot>

                        <x-slot:actions>
                            <x-action-message v-if="form.recentlySuccessful" class="mr-3">
                                {{ __('Created.') }}
                            </x-action-message>

                            <x-splade-submit :label="__('Create')" />
                        </x-slot>
                    </x-form-section>
                </x-splade-form>

                @if(count($tokens) > 0)
                    <x-section-border />

                    <!-- Manage API Tokens -->
                    <div class="mt-10 sm:mt-0">
                        <x-action-section>
                            <x-slot:title>
                                {{ __('Manage API Tokens') }}
                            </x-slot>

                            <x-slot:description>
                                {{ __('You may delete any of your existing tokens if they are no longer needed.') }}
                            </x-slot>

                            <!-- API Token List -->
                            <x-slot:content>
                                <div class="space-y-6">
                                    @foreach($tokens as $token)
                                        <div class="flex items-center justify-between">
                                            <div class="break-all">
                                                {{ $token['name'] }}
                                            </div>

                                            <div class="flex items-center ml-2">
                                                @if($token['last_used_ago'])
                                                    <div class="text-sm text-gray-400">
                                                        {{ __('Last used') }} {{ $token['last_used_ago'] }}
                                                    </div>
                                                @endif

                                                @if(count($availablePermissions) > 0)
                                                    <Link
                                                        modal
                                                        href="{{ route('api-tokens.edit', $token['id']) }}"
                                                        class="cursor-pointer ml-6 text-sm text-gray-400 underline"
                                                    >
                                                        {{ __('Permissions') }}
                                                    </Link>
                                                @endif

                                                <x-splade-form
                                                    method="delete"
                                                    :action="route('api-tokens.destroy', $token['id'])"
                                                    :confirm-danger="__('Delete API Token')"
                                                    :confirm-text="__('Are you sure you would like to delete this API token?')"
                                                    :confirm-button="__('Delete')"
                                                    require-password
                                                >

                                                    <button type="submit" class="cursor-pointer ml-6 text-sm text-red-500">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </x-splade-form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </x-slot>
                        </x-action-section>
                    </div>
                @endif

                @if($newToken = session('flash.token'))
                    <x-splade-modal :close-button="false" name="token-modal" class="!p-0">
                        <x-dialog-modal>
                            <x-slot:title>
                                {{ __('API Token') }}
                            </x-slot>

                            <x-slot:content>
                                <div>
                                    {{ __('Please copy your new API token. For your security, it won\'t be shown again.') }}
                                </div>

                                <div class="mt-4 bg-gray-100 px-4 py-2 rounded font-mono text-sm text-gray-500 break-all">
                                    {{ $newToken }}
                                </div>
                            </x-slot:content>

                            <x-slot:footer>
                                <button type="button" class="text-sm text-gray-700" @click="modal.close">
                                    {{ __('Cancel') }}
                                </button>
                            </x-slot:footer>
                        </x-dialog-modal>
                    </x-splade-modal>

                    <x-splade-script>$splade.openPreloadedModal('token-modal')</x-splade-script>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
