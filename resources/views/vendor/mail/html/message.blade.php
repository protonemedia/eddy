<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            <img src="{{ asset('logo.png') }}" class="logo" alt="{{ config('app.name') }} Logo" />
        </x-mail::header>
    </x-slot>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} {{ config('app.name') }}.
            @lang('All rights reserved.')
        </x-mail::footer>
    </x-slot>
</x-mail::layout>
