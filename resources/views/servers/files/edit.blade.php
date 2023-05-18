<x-server-layout :$server :title="__('Edit File')">
    <x-action-section in-sidebar-layout modal-max-width="4xl">
        <x-slot:title>
            {{ $file->nameWithContext() }}
        </x-slot>

        <x-slot:description>
            {{ $file->path }}
        </x-slot>

        <x-slot:content>
            <x-splade-form method="PATCH" :action="$file->updateRoute($server)" :default="[
                'contents' => $contents,
            ]" class="space-y-4">
                <x-prism-editor name="contents" :language="$file->prismLanguage->value" />
                <x-splade-submit :label="__('Save')" />
            </x-splade-form>
        </x-slot>
    </x-action>
</x-server-layout>