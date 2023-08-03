<div {{ $attributes->class('flex flex-col') }}>
    <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-white">
        @svg($icon, 'h-5 w-5 flex-none text-green-400')
        {{ $title }}
    </dt>
    <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-200">
        <p class="flex-auto">{{ $slot }}</p>
    </dd>
</div>
