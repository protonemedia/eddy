<div class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0">
    <div class="flex w-full flex-grow flex-col items-center justify-center">
        <div>
            {{ $logo }}
        </div>

        <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>

    <x-footer class="mt-auto pb-4 pt-4 text-gray-600" />
</div>
