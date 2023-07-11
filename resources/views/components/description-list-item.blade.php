<div {{ $attributes->class('py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-4') }}>
    <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
    <dd class="mt-1 flex flex-row items-center text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $slot }}</dd>
</div>
