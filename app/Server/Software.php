<?php

namespace App\Server;

use App\Tasks;
use App\Tasks\Task;
use App\Tasks\UpdateAlternatives;
use Illuminate\Support\Str;

enum Software: string
{
    case Caddy2 = 'caddy2';
    case Composer2 = 'composer2';
    case MySql80 = 'mysql80';
    case Node18 = 'node18';
    case Php81 = 'php81';
    case Php82 = 'php82';
    case Redis6 = 'redis6';

    /**
     * Returns the default stack of software for a fresh server.
     */
    public static function defaultStack(): array
    {
        return [
            static::Caddy2,
            static::MySql80,

            // Redis should be installed before PHP
            static::Redis6,
            static::Php81,
            static::Php82,
            static::Composer2,
            static::Node18,
        ];
    }

    /**
     * Returns the description of the software.
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            static::Caddy2 => 'Caddy 2',
            static::Composer2 => 'Composer 2',
            static::MySql80 => 'MySQL 8.0',
            static::Node18 => 'Node 18',
            static::Php81 => 'PHP 8.1',
            static::Php82 => 'PHP 8.2',
            static::Redis6 => 'Redis 6',
        };
    }

    /**
     * Returns a Task that restarts the software.
     */
    public function restartTaskClass(): ?string
    {
        return match ($this) {
            static::Caddy2 => Tasks\ReloadCaddy::class,
            static::MySql80 => Tasks\RestartMySql::class,
            static::Php81 => Tasks\RestartPhp81::class,
            static::Php82 => Tasks\RestartPhp82::class,
            static::Redis6 => Tasks\RestartRedis::class,
            default => null,
        };
    }

    /**
     * Returns a Task that makes the software the CLI default.
     */
    public function updateAlternativesTask(): ?Task
    {
        return match ($this) {
            static::Php81 => new UpdateAlternatives('php', '/usr/bin/php8.1'),
            static::Php82 => new UpdateAlternatives('php', '/usr/bin/php8.2'),
            default => null,
        };
    }

    /**
     * Returns the matching PhpVersion enum for the software.
     */
    public function findPhpVersion(): ?PhpVersion
    {
        return match ($this) {
            static::Php81 => PhpVersion::Php81,
            static::Php82 => PhpVersion::Php82,
            default => null,
        };
    }

    /**
     * Returns the Blade view name to install the software.
     */
    public function getInstallationViewName(): string
    {
        return 'tasks.software.install-'.Str::replace('_', '-', $this->value);
    }
}
