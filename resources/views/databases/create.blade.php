<x-server-layout :$server :title="__('Add Database')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __("Add Database on server ':server'.", ['server' => $server->name]) }}
        </x-slot>

        <x-slot:content>
            <x-splade-script>
                $splade.generatePassword = function () { const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; return
                Array.from(crypto.getRandomValues(new Uint32Array(32))) .map((x) => charset[x % charset.length]) .join('') };
            </x-splade-script>

            <x-splade-form :action="route('servers.databases.store', $server)" default="{create_user: true}" class="space-y-4">
                <x-splade-input name="name" :label="__('Database')" autofocus />
                <x-splade-checkbox name="create_user" :label="__('Create user for new database')" />

                <x-splade-input v-show="form.create_user" name="user" :label="__('User')" />
                <x-splade-input v-show="form.create_user" name="password" :label="__('Password')">
                    <x-slot:append>
                        <button
                            @click="form.password = $splade.generatePassword()"
                            type="button"
                            class="text-gray-500 hover:text-gray-700 focus:text-gray-700 focus:outline-none"
                        >
                            @svg('heroicon-o-sparkles', 'h-5 w-5')
                        </button>
                    </x-slot>
                </x-splade-input>
                <x-splade-submit />
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-server-layout>
