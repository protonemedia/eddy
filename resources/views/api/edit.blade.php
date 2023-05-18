<!-- API Token Permissions Modal -->
<x-splade-modal :close-button="false" class="!p-0">
    <x-splade-form method="put" :action="route('api-tokens.update', $token['id'])" :default="['permissions' => $token['abilities']]">
        <x-dialog-modal>
            <x-slot:title>
                {{ __('API Token Permissions') }}
            </x-slot>

            <x-slot:content>
                <x-splade-checkboxes
                    name="permissions"
                    class="grid grid-cols-1 md:grid-cols-2 gap-1"
                    :options="array_combine($availablePermissions, $availablePermissions)"
                />
            </x-slot>

            <x-slot:footer>
                <x-splade-button type="button" secondary :label="__('Cancel')" @click="modal.close" />
                <x-splade-submit :label="__('Save')" class="ml-3" />
            </x-slot>
        </x-dialog-modal>
    </x-splade-form>
</x-splade-modal>