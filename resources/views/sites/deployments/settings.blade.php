<x-site-layout :site="$site" :title="__('Deployment Settings')">
    <x-action-section in-sidebar-layout>
        <x-slot:title>
            {{ __('Deployment Settings') }}
        </x-slot>

        <x-slot:content>
            <x-splade-form
                method="PATCH"
                :action="route('servers.sites.deployment-settings.update', [$server, $site])"
                :default="$site"
                class="space-y-4"
            >
                <x-splade-input
                    name="deploy_notification_email"
                    label="{{ __('Notification Email for Deploy URL') }}"
                    :help="__('The email address to send notifications to when the deploy URL is called and the deployment fails.')"
                />

                @if($site->zero_downtime_deployment)
                    <x-splade-input
                        name="deployment_releases_retention"
                        label="{{ __('Number of Releases to Retain') }}"
                        :help="__('The number of releases to retain on the server. The oldest releases will be deleted when a new release is deployed.')"
                    />

                    <x-prism-editor language="plain" name="shared_directories" :label="__('Shared Directories (one per line)')" :help="__('These directories will be shared between the old and new deployment.')" />
                    <x-prism-editor language="plain" name="shared_files" :label="__('Shared Files (one per line)')" :help="__('These files will be shared between the old and new deployment.')" />
                    <x-prism-editor language="plain" name="writeable_directories" :label="__('Writeable Directories (one per line)')" :help="__('These directories will be writeable by the webserver.')" />
                @endif

                <x-prism-editor
                    name="hook_before_updating_repository"
                    language="bash"
                    :label="__('Before Updating Repository')"
                    :help="__('This bash script will be executed before updating the repository.')"
                />

                <x-prism-editor
                    name="hook_after_updating_repository"
                    language="bash"
                    :label="__('After Updating Repository')"
                    :help="__('This bash script will be executed after updating the repository.')"
                />

                @if($site->zero_downtime_deployment)
                    <x-prism-editor
                        name="hook_before_making_current"
                        language="bash"
                        :label="__('Before Activating New Release')"
                        :help="__('This bash script will be executed before swapping the symlink to the new release.')"
                    />

                    <x-prism-editor
                        name="hook_after_making_current"
                        language="bash"
                        :label="__('After Activating New Release')"
                        :help="__('This bash script will be executed after swapping the symlink to the new release.')"
                    />
                @endif

                <x-splade-submit :label="__('Save')" />
            </x-splade-form>
        </x-slot>
    </x-action-section>
</x-site-layout>
