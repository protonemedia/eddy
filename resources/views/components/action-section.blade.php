@props(['inSidebarLayout' => false])

@php
    $gridView = ! $inSidebarLayout && ! Splade::isModalRequest();
@endphp

<div {{ $attributes->except('modal-*')->class($gridView ? 'md:grid md:grid-cols-3 md:gap-6' : '') }}>
    @if ($gridView)
        <x-section-title>
            <x-slot:title>
                {{ $title }}
            </x-slot>

            @isset($description)
                <x-slot:description>
                    {{ $description }}
                </x-slot>
            @endisset
        </x-section-title>
    @endif

    <div class="mt-5 md:col-span-2 md:mt-0">
        <div class="bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
            <x-splade-modal max-width="{{ $attributes->get('modal-max-width', '2xl') }}" :close-explicitly="$attributes->get('modal-close-explicitly', true)">
                @if (! $gridView && isset($title))
                    <div class="mb-4">
                        <h1 class="text-lg font-medium text-gray-900">
                            {{ $title }}
                        </h1>

                        @isset($description)
                            <p class="mt-1 text-sm text-gray-600">
                                {{ $description }}
                            </p>
                        @endisset
                    </div>
                @endif

                {{ $content }}
            </x-splade-modal>
        </div>
    </div>
</div>
