<div class="flex justify-between md:col-span-1">
    <div class="px-4 sm:px-0">
        <h3 class="text-lg font-medium text-gray-900">
            {{ $title }}
        </h3>

        @isset($description)
            <p class="mt-1 text-sm text-gray-600">
                {{ $description }}
            </p>
        @endisset
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? null }}
    </div>
</div>
