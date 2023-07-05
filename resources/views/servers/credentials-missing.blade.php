@seoTitle(__('New Server'))

<x-app-layout>
    <x-action-section>
        <x-slot:title>
            {{ __('New Server') }}
        </x-slot>

        <x-slot:content>
            <p>
                {{ __('Whoops! You haven\'t configured your DigitalOcean or Hetzner Cloud credentials yet. Don\'t worry, you can still create a new server. However, keep in mind that you\'ll only be able to provision a server at a custom provider.') }}
            </p>

            <div class="space-x-4">
                <x-splade-button type="link" modal href="{{ route('credentials.create', ['forServer' => true]) }}" class="mt-4 inline-block">
                    {{ __('Create Credentials') }}
                </x-splade-button>

                <x-splade-button
                    type="link"
                    secondary
                    keep-modal
                    href="{{ route('servers.create', ['withoutCredentials' => true]) }}"
                    class="mt-4 inline-block"
                >
                    {{ __('Continue without credentials') }}
                </x-splade-button>
            </div>
        </x-slot>
    </x-action-section>
</x-app-layout>
