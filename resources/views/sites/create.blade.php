<x-server-layout :$server :title="__('Add Site')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __("Add Site on server ':server'.", ['server' => $server->name]) }}
        </x-slot>

        <x-slot:content>
            <x-splade-form
                :action="route('servers.sites.store', $server)"
                :default="
                    [
                        'php_version' => array_keys($phpVersions)[0],
                        'zero_downtime_deployment' => true,
                        'type' => 'laravel',
                        'web_folder' => '/public',
                        'repository_branch' => 'main',
                        'deploy_key_uuid' => null,
                    ]
                "
            >
                <div class="space-y-4">
                    <x-splade-input name="address" :label="__('Hostname')" prepend="https://" autofocus />

                    <div class="grid grid-cols-2 gap-4">
                        <x-splade-select name="php_version" :label="__('PHP Version')" :options="$phpVersions" />
                        <x-splade-select name="type" :label="__('Site Type')" :options="$types" />
                    </div>

                    <div v-if="form.type != 'wordpress'" class="space-y-4">
                        <x-splade-input name="web_folder" :label="__('Web Folder')" />
                        <x-splade-checkbox name="zero_downtime_deployment" :label="__('Enable Zero Downtime Deployment')" />
                    </div>
                </div>

                <div v-if="form.type != 'wordpress'" class="space-y-4">
                    <div class="my-8 h-px bg-slate-200" />

                    @if ($hasGithubCredentials)
                        <x-splade-select name="repository_url" :label="__('Github Repository')" :remote-url="route('github.repositories')" />
                    @endif

                    <x-splade-input name="deploy_key_uuid" type="hidden" />
                    <x-splade-input name="repository_url" :label="__('Repository URL')" />
                    <x-splade-input name="repository_branch" :label="__('Repository Branch')" />

                    <x-splade-toggle>
                        <x-prism-viewer
                            copy-to-clipboard
                            v-if="!form.deploy_key_uuid"
                            :label="__('Public Key Server')"
                            :help="__('Make sure this key is added to Github or other repository provider.')"
                            :value="trim($server->user_public_key)"
                        />

                        <x-prism-viewer
                            copy-to-clipboard
                            v-if="form.deploy_key_uuid"
                            :label="__('Deploy Key')"
                            :help="__('Instead of adding the public key of the server, you can add this deploy key to Github or other repository provider.')"
                            :value="trim($deployKey->publicKey)"
                        />

                        <x-splade-button v-if="!form.deploy_key_uuid" @click.prevent="form.deploy_key_uuid = {{ Js::from($deployKeyUuid) }}" secondary>
                            {{ __('Use a Deploy Key') }}
                        </x-splade-button>

                        <x-splade-button v-if="form.deploy_key_uuid" @click.prevent="form.deploy_key_uuid = null" secondary>
                            {{ __('Use the Server\'s Public Key') }}
                        </x-splade-button>
                    </x-splade-toggle>
                </div>

                <x-splade-submit class="mt-8" :label="__('Deploy Now')" />
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-server-layout>
