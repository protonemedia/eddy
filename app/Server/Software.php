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
            self::Caddy2,
            self::MySql80,

            // Redis should be installed before PHP
            self::Redis6,
            self::Php81,
            self::Php82,
            self::Composer2,
            self::Node18,
        ];
    }

    /**
     * Returns the description of the software.
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::Caddy2 => 'Caddy 2',
            self::Composer2 => 'Composer 2',
            self::MySql80 => 'MySQL 8.0',
            self::Node18 => 'Node 18',
            self::Php81 => 'PHP 8.1',
            self::Php82 => 'PHP 8.2',
            self::Redis6 => 'Redis 6',
        };
    }

    /**
     * Returns a Task that restarts the software.
     */
    public function restartTaskClass(): ?string
    {
        return match ($this) {
            self::Caddy2 => Tasks\ReloadCaddy::class,
            self::MySql80 => Tasks\RestartMySql::class,
            self::Php81 => Tasks\RestartPhp81::class,
            self::Php82 => Tasks\RestartPhp82::class,
            self::Redis6 => Tasks\RestartRedis::class,
            default => null,
        };
    }

    /**
     * Returns a Task that makes the software the CLI default.
     */
    public function updateAlternativesTask(): ?Task
    {
        return match ($this) {
            self::Php81 => new UpdateAlternatives('php', '/usr/bin/php8.1'),
            self::Php82 => new UpdateAlternatives('php', '/usr/bin/php8.2'),
            default => null,
        };
    }

    /**
     * Returns the matching PhpVersion enum for the software.
     */
    public function findPhpVersion(): ?PhpVersion
    {
        return match ($this) {
            self::Php81 => PhpVersion::Php81,
            self::Php82 => PhpVersion::Php82,
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
