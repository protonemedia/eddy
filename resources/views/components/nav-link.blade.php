@props(['active' => false])

@php
    $classes = ($active ?? false)
        ? 'inline-flex items-center rounded-md bg-indigo-100 px-3 py-2 text-sm font-medium text-indigo-900'
        : 'inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-indigo-900 hover:bg-indigo-50';
@endphp

<Link {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</Link>
