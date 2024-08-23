<div
    class="relative flex min-h-screen flex-col items-center justify-center bg-gradient-to-b from-gray-800 to-gray-900 selection:bg-red-500 selection:text-white"
>
    <div class="absolute left-1/2 top-0 ml-[-50%] h-[50vh] w-full">
        <div
            class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-indigo-700 opacity-70 [mask-image:radial-gradient(farthest-side_at_top,white,transparent)]"
        ></div>
    </div>

    <div class="z-10 mx-auto flex max-w-7xl flex-grow flex-col items-center justify-center p-6 lg:p-8">
        <div class="flex flex-col justify-center">
            <x-application-logo-white class="h-24 w-full sm:h-32 sm:w-auto" />

            <p class="mt-12 max-w-3xl text-center text-2xl font-medium leading-relaxed text-white sm:text-3xl sm:leading-relaxed">
                The open-source solution for seamless server provisioning and zero-downtime PHP deployment.
            </p>
        </div>

        <div class="mt-12 flex justify-center space-x-4">
            @auth
                <Link
                    href="{{ route('servers.index') }}"
                    class="rounded-md border border-transparent bg-indigo-500 px-4 py-2 font-bold text-white shadow-sm hover:bg-indigo-700 focus:border-indigo-300 focus:outline-none focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                    Dashboard
                </Link>
            @endauth

            @guest
                <Link
                    href="{{ route('login') }}"
                    class="rounded-md border border-transparent bg-indigo-500 px-4 py-2 font-bold text-white shadow-sm hover:bg-indigo-700 focus:border-indigo-300 focus:outline-none focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                    Log in
                </Link>
                @if (Route::has('register'))
                <Link
                    href="{{ route('register') }}"
                    class="rounded-md border border-transparent bg-indigo-500 px-4 py-2 font-bold text-white shadow-sm hover:bg-indigo-700 focus:border-indigo-300 focus:outline-none focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                    Register
                </Link>
                @endif
            @endguest
        </div>
    </div>

    <x-footer class="mt-auto pb-8 pt-12 text-gray-300" />
</div>

@include('welcome.features.provisioning')
@include('welcome.features.site')
@include('welcome.features.open-source')
@include('welcome.features.editor')
@include('welcome.features.tls')
@include('welcome.features.backups')
@include('welcome.pricing')

<div class="relative flex flex-col items-center justify-center bg-gradient-to-t from-gray-900 to-gray-800 px-8 selection:bg-red-500 selection:text-white">
    <x-footer class="mt-auto pb-8 pt-12 text-gray-300" />
</div>
