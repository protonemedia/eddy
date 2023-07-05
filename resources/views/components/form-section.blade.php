<div {{ $attributes->class('md:grid md:grid-cols-3 md:gap-6') }}>
    <x-section-title>
        <x-slot:title>
            {{ $title }}
        </x-slot>

        <x-slot:description>
            {{ $description }}
        </x-slot>
    </x-section-title>

    <div class="mt-5 md:col-span-2 md:mt-0">
        <div
            @class([
                'bg-white px-4 py-5 shadow sm:p-6',
                'sm:rounded-tl-md sm:rounded-tr-md' => isset($actions),
                'sm:rounded-md' => ! isset($actions),
            ])
        >
            <div class="grid grid-cols-6 gap-6">
                {{ $form }}
            </div>
        </div>

        @isset($actions)
            <div class="flex items-center justify-end bg-gray-50 px-4 py-3 text-right shadow sm:rounded-bl-md sm:rounded-br-md sm:px-6">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
