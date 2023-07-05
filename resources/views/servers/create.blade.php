@seoTitle(__('New Server'))

<x-app-layout>
    <x-action-section>
        <x-slot:title>
            {{ __('New Server') }}
        </x-slot>

        <x-slot:description>
            {{ __('Configure your new server.') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form
                :action="route('servers.store')"
                class="space-y-4"
                :default="
                    [
                        'credentials_id' => $defaultCredentials,
                        'custom_server' => $credentials->isEmpty(),
                    ]
                "
            >
                <x-splade-input name="name" :label="__('Name')" />

                <x-splade-checkbox
                    name="custom_server"
                    :disabled="$credentials->isEmpty()"
                    :label="__('Use a custom server provider')"
                    :help="__('Provision a fresh Ubuntu 22.04 server that you have root access to.')"
                />

                @if ($credentials->isNotEmpty())
                    <x-splade-select v-if="!form.custom_server" name="credentials_id" :label="__('Provider')" :options="$credentials" />
                @endif

                <x-splade-input v-if="form.custom_server" name="public_ipv4" :label="__('Public IPv4')" />

                <div v-if="form.credentials_id && !form.custom_server" class="space-y-4">
                    <x-splade-select name="region" :label="__('Region')" remote-url="`/servers/provider/${form.credentials_id}/regions`" />
                    <x-splade-select
                        v-if="form.region"
                        name="type"
                        :label="__('Type')"
                        remote-url="`/servers/provider/${form.credentials_id}/types/${form.region}`"
                    />
                    <x-splade-select
                        v-if="form.region"
                        name="image"
                        :label="__('Image')"
                        remote-url="`/servers/provider/${form.credentials_id}/images/${form.region}`"
                    />
                </div>

                <x-splade-select
                    name="ssh_keys[]"
                    multiple
                    :label="__('SSH Keys')"
                    :options="$sshKeys"
                    :help="__('Select the keys that should be added to the server so you can access it via SSH.')"
                />

                @if ($hasGithubCredentials)
                    <x-splade-checkbox
                        name="add_key_to_github"
                        :label="__('Add Server\'s SSH Key to Github')"
                        :help="__('If you want this server to be able to access your Github repositories, you can add the server\'s SSH key to your Github account.')"
                    />
                @endif

                <x-splade-submit />
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-app-layout>
