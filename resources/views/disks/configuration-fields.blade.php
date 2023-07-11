<div v-show="form.filesystem_driver === 's3'" class="space-y-4">
    <x-splade-checkbox name="configuration.s3_custom_endpoint" :label="__('Use a custom endpoint')" />
    <x-splade-input name="configuration.s3_bucket" :label="__('Bucket')" />
    <x-splade-input name="configuration.s3_access_key" :label="__('Access Key')" />
    <x-splade-input
        name="configuration.s3_secret_key"
        :label="__('Secret Key')"
        :help="$secretsRequired ? __('Only fill in the secret key if you want to change it.') : ''"
    />
    <x-splade-input name="configuration.s3_region" :label="__('Region')" />
    <x-splade-input v-show="form.configuration.s3_custom_endpoint" name="configuration.s3_endpoint" :label="__('Endpoint')" />
    <x-splade-checkbox name="configuration.s3_path_style_endpoint" :label="__('Use path-style endpoint')" />
</div>

<div v-show="form.filesystem_driver === 'sftp'" class="space-y-4">
    <x-splade-checkbox name="configuration.sftp_use_ssh_key" :label="__('Use the Server\'s Public Key')" />
    <x-splade-input name="configuration.sftp_host" :label="__('Host')" />
    <x-splade-input name="configuration.sftp_username" :label="__('Username')" />
    <x-splade-input
        name="configuration.sftp_password"
        v-show="!form.configuration.sftp_use_ssh_key"
        :label="__('Password')"
        :help="$secretsRequired ? __('Only fill in the password if you want to change it.') : ''"
    />
</div>

<div v-show="form.filesystem_driver === 'ftp'" class="space-y-4">
    <x-splade-input name="configuration.ftp_host" :label="__('Host')" />
    <x-splade-input name="configuration.ftp_username" :label="__('Username')" />
    <x-splade-input
        name="configuration.ftp_password"
        :label="__('Password')"
        :help="$secretsRequired ? __('Only fill in the password if you want to change it.') : ''"
    />
</div>
