<?php

namespace App\Models;

use App\FilesystemDriver;
use App\UlidGenerator;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @property Collection<int, Backup> $backups
 * @property Team $team
 */
class Disk extends Model
{
    use HasFactory;
    use HasUlids;

    protected $casts = [
        'filesystem_driver' => FilesystemDriver::class,
        'configuration' => AsEncryptedArrayObject::class,
    ];

    protected $fillable = [
        'name',
        'filesystem_driver',
        'configuration',
    ];

    /**
     * Generate a new ULID for the model.
     *
     * @return string
     */
    public function newUniqueId()
    {
        return (new UlidGenerator)->generate();
    }

    /**
     * Returns the secret keys of the configuration, for example, to
     * exclude the corresponding values from the edit form.
     */
    public static function secretConfigurationKeys(): array
    {
        return [
            's3_secret_key',
            'sftp_password',
            'ftp_password',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function backups()
    {
        return $this->hasMany(Backup::class);
    }

    /**
     * Returns the name of the filesystem driver.
     */
    public function getFilesystemDriverNameAttribute(): string
    {
        return $this->filesystem_driver->name;
    }

    /**
     * Returns the path of the configuration file on the server.
     */
    public function configPathOnServer(Server $server): string
    {
        return "/home/{$server->username}/{$server->working_directory}/disk-{$this->id}.json";
    }

    /**
     * Returns the configuration for Laravel's Filesystem Storage Builder.
     */
    public function configurationForStorageBuilder(Server $server): array
    {
        $config = Arr::mapWithKeys((array) $this->configuration, function ($value, $key) {
            $key = Str::after($key, '_');

            return [$key => $value];
        });

        if ($config['use_ssh_key'] ?? false) {
            $config['privateKey'] = "/home/{$server->username}/.ssh/id_rsa.pub";
            unset($config['password'], $config['use_ssh_key']);
        }

        $config['driver'] = $this->filesystem_driver->value;
        $config['throw'] = true;

        return $config;
    }
}
