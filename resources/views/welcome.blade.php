<div class="relative flex flex-col justify-center items-center min-h-screen bg-gradient-to-b from-gray-800 to-gray-900 selection:bg-red-500 selection:text-white">
    <div class="absolute left-1/2 top-0 ml-[-50%] h-[50vh] w-full">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-indigo-700 opacity-70 [mask-image:radial-gradient(farthest-side_at_top,white,transparent)]"></div>
    </div>

    <div class="flex-grow flex flex-col justify-center items-center max-w-7xl mx-auto p-6 lg:p-8 z-10">
        <div class="flex flex-col justify-center">
            <x-application-logo-white class="w-full sm:w-auto h-24 sm:h-32" />

            <p class="text-2xl sm:text-3xl max-w-3xl text-center leading-relaxed sm:leading-relaxed font-medium mt-12 text-white">
                The open-source solution for seamless server provisioning and zero-downtime PHP deployment.
            </p>
        </div>

        <div class="flex justify-center mt-12 space-x-4">
            @auth
                <Link href="{{ route('servers.index') }}" class="border rounded-md shadow-sm font-bold py-2 px-4 focus:outline-none focus:ring focus:ring-opacity-50 bg-indigo-500 hover:bg-indigo-700 text-white border-transparent focus:border-indigo-300 focus:ring-indigo-200">Dashboard</Link>
            @else
                <Link href="{{ route('login') }}" class="border rounded-md shadow-sm font-bold py-2 px-4 focus:outline-none focus:ring focus:ring-opacity-50 bg-indigo-500 hover:bg-indigo-700 text-white border-transparent focus:border-indigo-300 focus:ring-indigo-200">Log in</Link>
                <Link href="{{ route('register') }}" class="border rounded-md shadow-sm font-bold py-2 px-4 focus:outline-none focus:ring focus:ring-opacity-50 bg-indigo-500 hover:bg-indigo-700 text-white border-transparent focus:border-indigo-300 focus:ring-indigo-200">Register</Link>
            @endauth
        </div>
    </div>

    <x-footer class="mt-auto pt-12 pb-8 text-gray-300" />
</div>

@include('welcome.features.provisioning')
@include('welcome.features.site')
@include('welcome.features.open-source')
@include('welcome.features.editor')
@include('welcome.features.tls')
@include('welcome.pricing')

<div class="relative flex flex-col justify-center items-center bg-gradient-to-t from-gray-900 to-gray-800 selection:bg-red-500 selection:text-white px-8">
    <x-footer class="mt-auto pt-12 pb-8 text-gray-300" />
</div>
