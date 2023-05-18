<?php

namespace App\Server;

use Illuminate\Support\Collection;

enum PhpVersion: string
{
    case Php81 = 'php81';
    case Php82 = 'php82';

    /**
     * Returns a pretty formatted name, something like 'PHP 8.1'
     */
    public function getDisplayName(): string
    {
        return "PHP {$this->formattedVersion()}";
    }

    /**
     * Returns the version number, something like '8.1'
     */
    public function formattedVersion(): string
    {
        return "{$this->value[3]}.{$this->value[4]}";
    }

    /**
     * Returns a key-value array with all PHP versions.
     */
    public static function named(array $cases = null): array
    {
        $cases = is_null($cases) ? self::cases() : $cases;

        return Collection::make($cases)->reverse()->mapWithKeys(function (object $item) {
            return [$item->value => $item->getDisplayName()];
        })->all();
    }

    /**
     * Returns the binary location for the PHP version.
     */
    public function getBinary(): string
    {
        return match ($this) {
            self::Php81 => '/usr/bin/php8.1',
            self::Php82 => '/usr/bin/php8.2',
        };
    }

    /**
     * Returns socket path for the PHP version.
     */
    public function getSocket(): string
    {
        return match ($this) {
            self::Php81 => '/run/php/php8.1-fpm.sock',
            self::Php82 => '/run/php/php8.2-fpm.sock',
        };
    }
}
