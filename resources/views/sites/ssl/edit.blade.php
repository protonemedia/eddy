<x-splade-event private channel="teams.{{ auth()->user()->currentTeam->id }}" listen="SiteUpdated" preserve-scroll />

<x-site-layout :site="$site" :title="__('SSL Settings')">
    <x-action-section in-sidebar-layout>
        <x-slot:content>
            @if($site->pending_tls_update_since)
                {{ __('Your SSL settings are being updated. This may take a few minutes.') }}
            @else
                <x-splade-form
                    method="PATCH"
                    :action="route('servers.sites.ssl.update', [$server, $site])"
                    :default="$site"
                    class="space-y-4"
                    :confirm="__('Are you sure you want to update the SSL settings?')"
                    :confirm-text="__('The Caddyfile will be updated automatically.')"
                >
                    <x-splade-radio name="tls_setting" :value="\App\Models\TlsSetting::Auto->value">
                        <p class="font-medium text-gray-900">{{ __('Auto') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('Caddy automatically obtains and renews your site\'s TLS certificate using a public ACME CA such as Let\'s Encrypt.') }}</p>
                    </x-splade-radio>

                    <x-splade-radio name="tls_setting" :value="\App\Models\TlsSetting::Internal->value">
                        <p class="font-medium text-gray-900">{{ __('Internal') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('The TLS certificate for your site is generated internally, rather than relying on an external certificate authority. Useful for development environments.') }}</p>
                    </x-splade-radio>

                    <x-splade-radio name="tls_setting" :value="\App\Models\TlsSetting::Custom->value">
                        <p class="font-medium text-gray-900">{{ __('Custom') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('You provide your own TLS certificate and key for your site.') }}</p>
                    </x-splade-radio>

                    <x-splade-radio name="tls_setting" :value="\App\Models\TlsSetting::Off->value">
                        <p class="font-medium text-gray-900">{{ __('Off') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('Turn off TLS for this site.') }}</p>
                    </x-splade-radio>

                    <div v-show="form.tls_setting === '{{ \App\Models\TlsSetting::Custom->value }}'" class="space-y-4">
                        @if($site->activeCertificate)
                            <p>
                                {{ __('You may find the current certificate on the server at:') }}
                                <span class="font-mono text-sm">{{ $site->activeCertificate->siteDirectory() }}</span>
                            </p>
                        @endif

                        <x-prism-editor language="plain" name="private_key" :label="__('Private Key')" />
                        <x-prism-editor language="plain" name="certificate" :label="__('Certificate')"
                            :help="$site->activeCertificate ? __('Only fill the fields if you want to replace the current certificate.') : ''" />
                    </div>

                    <x-splade-submit :label="__('Save')" />
                </x-splade-form>
            @endif
        </x-slot>
    </x-action-section>



</x-site-layout>
