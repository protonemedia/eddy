<x-server-layout :$server :title="__('View File')">
    <x-action-section in-sidebar-layout modal-max-width="4xl">
        <x-slot:title>
            {{ $file->nameWithContext() }}
        </x-slot>

        <x-slot:description>
            {{ $file->path }}
        </x-slot>

        <x-slot:content>
            <x-splade-form
                method="GET"
                :action="$file->showRoute($server)"
                :default="['lines' => $lines]"
                class="flex flex-row items-end space-x-4 mb-4"
                keep-modal
            >
                <x-splade-input name="lines" min="1" max="1000" type="number" :append="__('Lines')" class="max-w-fit" />
                <x-splade-submit secondary :label="__('Refresh')" />
            </x-splade-form>

            <x-splade-lazy>
                <x-slot:placeholder>
                    <div class="flex flex-row items-center">
                        {{ __('Retrieving the file from the server...') }}
                        @svg('heroicon-s-cog-6-tooth', 'h-5 w-5 text-gray-400 ml-2 animate-spin')
                    </div>
                </x-slot:placeholder>

                <x-prism-viewer :value="$contents" :language="$file->prismLanguage->value" />
            </x-splade-lazy>
        </x-slot>
    </x-action>
</x-server-layout>