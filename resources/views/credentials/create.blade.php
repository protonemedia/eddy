@seoTitle(__('Add Credentials'))

<x-app-layout>
    <x-action-section>
        <x-slot:title>
            {{ __('Add Credentials') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form :action="route('credentials.store', ['forServer' => $forServer])" :default="['bind_to_team' => true]" class="space-y-4">
                <p>
                    {{ __('Credentials belong to your profile and are personal. Team members can not see or use your credentials.') }}
                </p>

                <x-splade-select name="provider" :label="__('Provider')" :options="$providers" />
                <x-splade-input v-show="form.provider != 'github'" name="name" :label="__('Name')" :placeholder="__('John\'s credentials')" />

                <x-splade-checkbox
                    v-show="form.provider != 'github'"
                    name="bind_to_team"
                    :label="__('Bind credentials to current team (:team)', ['team' => auth()->user()->currentTeam->name])"
                    :help="__('By binding the new credentials to your current team, you can only use them for this team. Uncheck this option to make the credentials available for teams you are a member of.')"
                />

                <x-splade-textarea
                    autosize
                    v-show="form.provider == 'digital_ocean'"
                    name="credentials.digital_ocean_token"
                    :label="__('API Token')"
                />

                <x-splade-textarea
                    autosize
                    v-show="form.provider == 'hetzner_cloud'"
                    name="credentials.hetzner_cloud_token"
                    :label="__('API Token')"
                />

                <p v-if="form.provider == 'github'">
                    {{ __('Connecting to Github will allow you to quickly select repositories and branches when deploying new sites.') }}
                </p>

                <p v-else-if="form.provider == 'digital_ocean'">
                    {!! __('You may generate a new API token by visiting the API section of your DigitalOcean control panel. Make sure to select the read and write scopes. <a class="underline" target="_blank" href=":link">Learn more</a>.', ['link' => 'https://docs.digitalocean.com/reference/api/create-personal-access-token/']) !!}
                </p>

                <p v-else-if="form.provider == 'hetzner_cloud'">
                    {!! __('You may generate an API token in the Hetzner Cloud Console. Go to Security on the left menu bar, and then go to the Api Tokens tab. Make sure to select the Read & Write permission. <a class="underline" target="_blank" href=":link">Learn more</a>.', ['link' => 'https://docs.hetzner.com/cloud/api/getting-started/generating-api-token/']) !!}
                </p>

                <div>
                    <x-splade-submit v-show="form.provider != 'github'" />

                    <x-splade-button v-show="form.provider == 'github'" type="link" href="{{ route('github.redirect') }}" away class="inline-block">
                        {{ __('Connect to Github') }}
                    </x-splade-button>
                </div>
            </x-splade-form>
        </x-slot>
    </x-action>
</x-app-layout>
