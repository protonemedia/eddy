@props(['as' => false, 'active' => false])

@php
    $classes = ($active ?? false)
        ? 'block w-full border-l-4 border-indigo-400 bg-indigo-50 py-2 pl-3 pr-4 text-left text-base font-medium text-indigo-700 transition duration-150 ease-in-out focus:border-indigo-700 focus:bg-indigo-100 focus:text-indigo-800 focus:outline-none'
        : 'block w-full border-l-4 border-transparent py-2 pl-3 pr-4 text-left text-base font-medium text-gray-600 transition duration-150 ease-in-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 focus:border-gray-300 focus:bg-gray-50 focus:text-gray-800 focus:outline-none';
@endphp

@if ($as === 'button')
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@else
    <Link {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </Link>
@endif
