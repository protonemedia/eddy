<x-app-layout>
    <x-splade-data store="sidebar" default="{ opened: false }" />

    {{-- Passes the following slots to the app layout: --}}
    @isset($header)
        <x-slot:header>
            <div class="flex flex-row items-center justify-between sm:justify-start">
                <div>{{ $header }}</div>

                <button @click.prevent="sidebar.opened = !sidebar.opened" class="lg:hidden">
                    @svg('heroicon-o-bars-3', 'ml-2 h-5 w-5 text-gray-400 hover:text-gray-500')
                </button>
            </div>
        </x-slot>
    @endisset

    @isset($description)
        <x-slot:description> {{ $description }} </x-slot>
    @endisset

    @isset($actions)
        <x-slot:actions> {{ $actions }} </x-slot>
    @endisset

    <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
        <aside class="mt-4 lg:mt-0 lg:col-span-3 lg:block" :class="{ 'hidden': !sidebar.opened, 'block': sidebar.opened }">
            <nav class="space-y-1 px-2 sm:px-0 bg-white lg:bg-transparent sm:rounded-md shadow-sm lg:shadow-none -mt-4 mb-4 lg:my-0 border border-gray-200 lg:border-0">
                @foreach($navigationItems as $item)
                    <Link
                        href="{{ $href($item) }}"
                        class="@if($item->current) bg-gray-50 text-indigo-600 hover:bg-white @else text-gray-900 hover:bg-gray-50 hover:text-gray-900 @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium"
                    >
                        @svg($item->icon, $item->current ? 'text-indigo-500 -ml-1 mr-3 h-6 w-6 flex-shrink-0' : 'text-gray-400 group-hover:text-gray-500 -ml-1 mr-3 h-6 w-6 flex-shrink-0')
                        <span class="truncate">{{ $item->label }}</span>
                    </Link>
                @endforeach
            </nav>
        </aside>

        <div class="lg:col-span-9">
            {{ $slot }}
        </div>
    </div>
</x-app-layout>