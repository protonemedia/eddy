<?php

namespace App\Http\Requests;

use App\FilesystemDriver;
use Illuminate\Support\Arr;

/**
 * @mixin \Illuminate\Foundation\Http\FormRequest
 *
 * @codeCoverageIgnore Handled by Dusk tests.
 */
trait DiskConfigurationRules
{
    public function prepareForValidation(): void
    {
        $configuration = $this->input('configuration', []);

        /** @var FilesystemDriver */
        $filesystemDriver = $this->enum('filesystem_driver', FilesystemDriver::class);

        if ($filesystemDriver === FilesystemDriver::S3 && is_array($configuration)) {
            $configuration['s3_path_style_endpoint'] = $this->boolean('configuration.s3_path_style_endpoint');

            if (! Arr::get($configuration, 's3_custom_endpoint')) {
                unset($configuration['s3_endpoint']);
            }

            $this->merge([
                'configuration' => $configuration,
            ]);
        }

        if ($filesystemDriver === FilesystemDriver::SFTP && is_array($configuration)) {
            $configuration['sftp_use_ssh_key'] = $this->boolean('configuration.sftp_use_ssh_key');

            if ($configuration['sftp_use_ssh_key']) {
                $configuration['sftp_password'] = null;
            }

            $this->merge([
                'configuration' => $configuration,
            ]);
        }
    }

    protected function rulesForFilesystemDriver(FilesystemDriver $filesystemDriver = null): array
    {
        $rules = match ($filesystemDriver) {
            FilesystemDriver::S3 => $this->rulesForS3(),
            FilesystemDriver::SFTP => $this->rulesForSftp(),
            FilesystemDriver::FTP => $this->rulesForFtp(),
            default => []
        };

        return Arr::prependKeysWith($rules, 'configuration.');
    }

    protected function rulesForS3(): array
    {
        return [
            's3_bucket' => ['required', 'string'],
            's3_access_key' => ['required', 'string'],
            's3_secret_key' => ['required', 'string'],
            's3_region' => ['required', 'string'],
            's3_endpoint' => ['nullable', 'string'],
            's3_path_style_endpoint' => ['nullable', 'boolean'],
        ];
    }

    protected function rulesForSftp(): array
    {
        return [
            'sftp_host' => ['required', 'string'],
            'sftp_username' => ['required', 'string'],
            'sftp_password' => ['nullable', 'required_without:configuration.sftp_use_ssh_key', 'string'],
            'sftp_use_ssh_key' => ['nullable', 'boolean'],
        ];
    }

    protected function rulesForFtp(): array
    {
        return [
            'ftp_host' => ['required', 'string'],
            'ftp_username' => ['required', 'string'],
            'ftp_password' => ['required', 'string'],
        ];
    }
}
