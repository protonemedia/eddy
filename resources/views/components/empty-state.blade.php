<Link {{ $attributes->class('relative block w-full py-12 text-center focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2') }}>
    @svg($icon ?? 'heroicon-o-squares-plus', 'mx-auto h-12 w-12 text-gray-400')

    <span class="mt-2 block text-sm font-semibold text-gray-900 flex flex-row items-center justify-center">
        {{ $slot }}
    </span>
</Link>
