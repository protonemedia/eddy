<x-banner />

<div class="min-h-screen bg-gray-100">
    <x-navigation />

    <!-- Page Heading -->
    @isset($header)
        <header class="max-w-7xl mx-auto pt-6 px-4 sm:px-3 md:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-semibold text-gray-900">
                    {{ $header }}
                </h1>

                @isset($description)
                    <p class="mt-1 sm:mt-3 text-sm text-gray-700">{{ $description }}</p>
                @endisset
            </div>

            @isset($actions)
                <div class="mt-4 inline-flex sm:block">
                    {{ $actions }}
                </div>
            @endisset
        </header>
    @endif

    <!-- Page Content -->
    <main class="py-4 sm:py-6 max-w-7xl mx-auto sm:px-3 md:px-6 lg:px-8">
        {{ $slot }}
    </main>
</div>