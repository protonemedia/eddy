<div class="relative flex flex-col items-center justify-center bg-gradient-to-l from-gray-800 to-gray-900 px-8 py-24 selection:bg-red-500 selection:text-white">
    <div class="flex max-w-5xl grid-cols-3 flex-col-reverse items-center gap-16 md:grid">
        <img src="{{ asset('features/site.png') }}" alt="Site management at Eddy Server Management" class="rounded shadow-md sm:w-1/2 md:w-full" />

        <div class="prose-xl text-gray-100 md:col-span-2">
            <h2 class="text-green-400">Zero Downtime Deployment</h2>
            <p>
                Our platform offers a seamless Zero Downtime Deployment solution for
                <b class="text-white">Laravel, PHP, static, and Wordpress sites</b>
                , with a variety of customizable deployment options. Manage cronjobs, firewall rules, and MySQL 8 with ease, plus SSH key management and
                background daemon support.
                <b class="text-white">Deploy with confidence!</b>
            </p>
        </div>
    </div>

    <x-feature-grid>
        <x-feature icon="heroicon-o-adjustments-horizontal" title="Caddy Web Server">
            Caddy is our web server of choice. It's fast, secure, and easy to configure. We provide a near-perfect Caddy configuration for each site type.
        </x-feature>
        <x-feature icon="heroicon-o-forward" title="Zero Downtime Deployment">
            Nobody likes downtime. That's why Eddy supports Zero Downtime Deployment out of the box. Deploy your sites with confidence, even on Fridays!
        </x-feature>
        <x-feature icon="heroicon-o-lock-closed" title="SSL Certificates">
            While you can bring your own SSL certificates, Caddy can also automatically provision free SSL certificates using Let's Encrypt or ZeroSSL and
            automatically renew them.
        </x-feature>
        <x-feature icon="heroicon-o-check-badge" title="Optimized per Site">
            Eddy comes with built-in support for Laravel, generic PHP, static, and Wordpress sites. It provides a starting point for each site type and can be
            customized to your needs.
        </x-feature>
        <x-feature icon="heroicon-o-cursor-arrow-rays" title="Push to Deploy">
            Deploying your sites from the UI is just a click away. But you can also use version control or CI/CD tools to deploy your sites automatically!
        </x-feature>
        <x-feature icon="heroicon-o-bolt" title="PHP version per Site">
            Not all your sites might be on the same PHP version. Eddy allows you to choose the a version per site, so you can maintain sites at your own pace
            while still keeping them on the same server.
        </x-feature>
    </x-feature-grid>
</div>
