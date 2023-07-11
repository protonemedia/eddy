<?php

namespace Database\Factories;

use App\FilesystemDriver;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Disk>
 */
class DiskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'My Backup Disk',
            'filesystem_driver' => FilesystemDriver::FTP,
            'user_id' => UserFactory::new(),
            'configuration' => [
                'ftp_host' => 'ftp.example.com',
                'ftp_username' => 'username',
                'ftp_password' => 'password',
            ],
        ];
    }

    public function s3()
    {
        return $this->state(function (array $attributes) {
            return [
                'filesystem_driver' => FilesystemDriver::S3,
                'configuration' => [
                    's3_bucket' => 'my-bucket',
                    's3_region' => 'eu-west-1',
                    's3_access_key' => 'my-access-key',
                    's3_secret_key' => 'my-secret-key',
                ],
            ];
        });
    }

    public function ftp()
    {
        return $this->state(function (array $attributes) {
            return [
                'filesystem_driver' => FilesystemDriver::FTP,
                'configuration' => [
                    'ftp_host' => 'ftp.example.com',
                    'ftp_username' => 'username',
                    'ftp_password' => 'password',
                ],
            ];
        });
    }

    public function sftp()
    {
        return $this->state(function (array $attributes) {
            return [
                'filesystem_driver' => FilesystemDriver::SFTP,
                'configuration' => [
                    'sftp_host' => 'sftp.example.com',
                    'sftp_username' => 'username',
                    'sftp_password' => 'password',
                ],
            ];
        });
    }

    public function sftpSsh()
    {
        return $this->state(function (array $attributes) {
            return [
                'filesystem_driver' => FilesystemDriver::SFTP,
                'configuration' => [
                    'sftp_host' => 'sftp.example.com',
                    'sftp_username' => 'username',
                    'sftp_use_ssh_key' => true,
                ],
            ];
        });
    }

    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
                'team_id' => $user->currentTeam?->id,
            ];
        });
    }

    public function forTeam(Team $team)
    {
        return $this->state(function (array $attributes) use ($team) {
            return [
                'team_id' => $team->id,
            ];
        });
    }
}
