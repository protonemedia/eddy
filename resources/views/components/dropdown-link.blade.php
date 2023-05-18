@props(['as' => false])

<div>
    @if($as === 'button')
        <button {{ $attributes->merge(['type' => 'submit', 'class' => 'block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out']) }}>
            {{ $slot }}
        </button>
    @elseif($as === 'a')
        <a {{ $attributes->merge(['class' => 'block px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out']) }}>
            {{ $slot }}
        </a>
    @else
        <Link {{ $attributes->merge(['class' => 'block px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out']) }}>
            {{ $slot }}
        </Link>
    @endif
</div>