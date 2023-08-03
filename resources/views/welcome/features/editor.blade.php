<div class="relative flex flex-col items-center justify-center bg-gradient-to-b from-gray-800 to-gray-900 px-8 py-24 selection:bg-red-500 selection:text-white">
    <div class="flex max-w-5xl grid-cols-3 flex-col items-center gap-16 md:grid">
        <div class="prose-xl col-span-2 text-gray-100">
            <h2 class="text-green-400">Easy-to-Use Interface</h2>
            <p>
                Our platform offers a user-friendly interface for effortless script configuration and deployment. You can easily edit configuration files and
                view log files through our interface. Plus, it's responsive, so you can access it on mobile devices too.
                <b class="text-white">Github integration</b>
                is available for easy code deployment from your repositories.
            </p>
        </div>

        <img src="{{ asset('features/editor.png') }}" alt="Easy-to-use interface at Eddy Server Management" class="rounded shadow-md sm:w-1/2 md:w-full" />
    </div>

    <x-feature-grid>
        <x-feature icon="heroicon-o-circle-stack" title="GitHub Integration">
            Once you've connected your GitHub account, we can add the server's SSH key to deploy your repositories. When you add a site, we'll automatically
            list your repositories.
        </x-feature>
        <x-feature icon="heroicon-o-document-check" title="Customize configuration files">
            Most of the important configuration files can be edited through the interface, including Caddy, PHP and MySQL configuration files. It even validates
            the syntax for you.
        </x-feature>
        <x-feature icon="heroicon-o-clock" title="Cronjobs and Daemons" class="sm:col-span-2 lg:col-span-1">
            Want to run scheduled tasks or background daemons like queues or websockets? No problem! You can easily add cronjobs and daemons through the
            interface.
        </x-feature>
    </x-feature-grid>
</div>
