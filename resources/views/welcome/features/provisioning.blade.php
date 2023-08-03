<div class="relative flex flex-col items-center justify-center bg-gradient-to-b from-gray-800 to-gray-900 px-8 py-24 selection:bg-red-500 selection:text-white">
    <h2 class="mb-16 text-5xl font-bold tracking-tight text-white sm:text-6xl">Eddy's Features</h2>

    <div class="flex max-w-5xl grid-cols-3 flex-col items-center gap-16 md:grid">
        <div class="prose-xl text-gray-100 md:col-span-2">
            <h2 class="text-green-400">Server Provisioning</h2>
            <p>
                Looking for a hassle-free solution to manage and deploy servers and websites?
                <b class="text-white">Eddy Server Management</b>
                has got you covered! Our cutting-edge service makes it super easy to create servers on
                <b class="text-white">Digital Ocean</b>
                and
                <b class="text-white">Hetzner Cloud</b>
                and provision them with the latest Ubuntu version. Try Eddy today!
            </p>
        </div>

        <img
            src="{{ asset('features/provisioning.png') }}"
            alt="Easy server provisioning at Eddy Server Management"
            class="rounded shadow-md sm:w-1/2 md:w-full"
        />
    </div>

    <x-feature-grid>
        <x-feature icon="heroicon-o-cpu-chip" title="Cloud Providers">
            Eddy supports Digital Ocean and Hetzner Cloud out of the box, but you can choose any cloud provider you want. All you need is a VPS with root
            access.
        </x-feature>
        <x-feature icon="heroicon-o-inbox-arrow-down" title="Software Installation">
            Installing and configuring software can be a pain. Eddy takes care of that for you. It installs Caddy, PHP, MySQL, Redis, and all the other tools
            you need.
        </x-feature>
        <x-feature icon="heroicon-o-command-line" title="You are in Control" class="sm:col-span-2 lg:col-span-1">
            While Eddy does all the work, you're fully in control. Not only can you see exactly what Eddy is doing due to its open source nature, but you can
            also customize it to your needs.
        </x-feature>
    </x-feature-grid>
</div>
