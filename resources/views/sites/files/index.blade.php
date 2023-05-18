<x-site-layout :site="$site" :title="__('Files')">
    <x-splade-table :for="$files">
        <x-splade-cell description>
            <p class="whitespace-pre-line">{{ $item->description }}</p>
        </x-splade-cell>
    </x-splade-table>
</x-site-layout>
