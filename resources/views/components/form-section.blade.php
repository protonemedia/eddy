<div {{ $attributes->class('md:grid md:grid-cols-3 md:gap-6') }}>
    <x-section-title>
        <x-slot:title>
            {{ $title }}
        </x-slot>

        <x-slot:description>
            {{ $description }}
        </x-slot>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="px-4 py-5 bg-white sm:p-6 shadow @isset($actions) sm:rounded-tl-md sm:rounded-tr-md @else sm:rounded-md @endif">
            <div class="grid grid-cols-6 gap-6">
                {{ $form }}
            </div>
        </div>

        @isset($actions)
            <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-right sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>

