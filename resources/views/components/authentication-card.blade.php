<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full flex-grow flex flex-col justify-center items-center">
        <div>
            {{ $logo }}
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>

    <x-footer class="mt-auto pt-4 pb-4 text-gray-600" />
</div>
