<x-banner />

<div class="min-h-screen bg-gray-100">
    <x-navigation />

    <!-- Page Heading -->
    @isset($header)
        <header class="mx-auto flex max-w-7xl flex-col justify-between px-4 pt-6 sm:flex-row sm:items-center sm:px-3 md:px-6 lg:px-8">
            <div>
                <h1 class="text-lg font-semibold text-gray-900 sm:text-xl">
                    {{ $header }}
                </h1>

                @isset($description)
                    <p class="mt-1 text-sm text-gray-700 sm:mt-3">{{ $description }}</p>
                @endisset
            </div>

            @isset($actions)
                <div class="mt-4 inline-flex sm:block">
                    {{ $actions }}
                </div>
            @endisset
        </header>
    @endisset

    <!-- Page Content -->
    <main class="mx-auto max-w-7xl py-4 sm:px-3 sm:py-6 md:px-6 lg:px-8">
        {{ $slot }}
    </main>
</div>
