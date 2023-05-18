<x-server-layout :server="$server" :title="__('Logs')">
    <x-splade-table :for="$logs">
        <x-splade-cell description>
            <p class="whitespace-pre-line">{{ $item->description }}</p>
        </x-splade-cell>
    </x-splade-table>
</x-server-layout>
