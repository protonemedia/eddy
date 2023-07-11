@seoTitle(__('Edit Backup'))

<x-server-layout :$server>
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __('Edit Backup') }}
        </x-slot>

        <x-slot:description>
            {{ __('Other team members can view this backup, but you\'re the only one who can edit it.') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form method="PATCH" :action="route('servers.backups.update', [$server, $backup])" class="space-y-4" :default="$backup">
                <div class="grid grid-cols-2 gap-4">
                    <x-splade-input name="name" :label="__('Name')" />
                    <x-splade-select name="disk_id" :label="__('Disk')" :options="$disks" />
                </div>
                <x-splade-select name="databases" :label="__('Databases')" :options="$databases" multiple />
                <x-prism-editor
                    language="plain"
                    name="include_files"
                    :label="__('Directories and Files')"
                    :help="__('Specify the full path. Separate multiple paths with a new line.')"
                />
                <x-prism-editor
                    v-show="form.include_files"
                    language="plain"
                    name="exclude_files"
                    :label="__('Exclude Directories and Files')"
                    :help="__('You may use wildcards like */.git/* to exclude directories or files.')"
                />
                <x-splade-input
                    name="retention"
                    label="{{ __('Number of backups to Retain') }}"
                    :help="__('The number of backups to retain on the disk. The oldest backups will be deleted first. Leave blank to retain all backups.')"
                />
                <x-splade-radios name="frequency" :label="__('Frequency')" :options="$frequencies" />
                <x-splade-input v-if="form.frequency == 'custom'" name="custom_expression" :label="__('Cron Expression')" />
                <x-splade-group :label="__('Notifications')" inline>
                    <x-splade-checkbox name="notification_on_failure" :label="__('Send on failure')" />
                    <x-splade-checkbox name="notification_on_success" :label="__('Send on success')" />
                </x-splade-group>
                <x-splade-input
                    v-if="form.notification_on_failure || form.notification_on_success"
                    name="notification_email"
                    :label="__('Notification Email')"
                />

                <div class="flex flex-row items-center justify-between">
                    <x-splade-submit />

                    <x-splade-link confirm-danger method="DELETE" :href="route('servers.backups.destroy', [$server, $backup])">
                        <x-splade-button danger :label="__('Delete Backup')" />
                    </x-splade-link>
                </div>
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-server-layout>
