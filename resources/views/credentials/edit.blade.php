@seoTitle(__('Edit Credentials'))

<x-app-layout>
    <x-action-section>
        <x-slot:title>
            {{ __('Edit Credentials') }}
        </x-slot>

        <x-slot:description>
            {{ __('Edit your credentials.') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form method="PATCH" :action="route('credentials.update', $credentials)" :default="$credentials" class="space-y-4">
                <x-splade-select disabled name="provider" :label="__('Provider')" :options="$providers" />
                <x-splade-input v-show="form.provider != 'github'" name="name" :label="__('Name')" />

                <x-splade-textarea
                    autosize
                    v-show="form.provider == 'digital_ocean'"
                    name="credentials.digital_ocean_token"
                    :label="__('API Token')"
                    :help="__('Only fill this field if you want to change the token.')"
                />

                <x-splade-textarea
                    autosize
                    v-show="form.provider == 'hetzner_cloud'"
                    name="credentials.hetzner_cloud_token"
                    :label="__('API Token')"
                    :help="__('Only fill this field if you want to change the token.')"
                />

                <div class="flex flex-row justify-between items-center">
                    <x-splade-submit v-show="form.provider != 'github'" :label="__('Save')" />

                    <x-splade-link confirm-danger method="DELETE" :href="route('credentials.destroy', $credentials)">
                        <x-splade-button danger :label="__('Delete Credentials')" />
                    </x-splade-link>
                </div>
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-app-layout>