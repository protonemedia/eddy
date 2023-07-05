<x-server-layout :$server>
    <x-slot:title>
        {{ __('Files') }}
    </x-slot>

    <x-slot:description>
        {{ __('Manage your Files.') }}
    </x-slot>

    <x-splade-table :for="$files">
        <x-splade-cell description>
            <p class="whitespace-pre-line">{{ $item->description }}</p>
        </x-splade-cell>
    </x-splade-table>
</x-server-layout>
