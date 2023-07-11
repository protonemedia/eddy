<?php

namespace App;

use Illuminate\Support\Str;

class UlidGenerator
{
    /**
     * The callback that should be used to generate ULIDs.
     *
     * @var callable|null
     */
    protected static $ulidFactory;

    /**
     * Set the callable that will be used to generate ULIDs.
     *
     * @return void
     */
    public static function createUuidsUsing(callable $factory = null)
    {
        static::$ulidFactory = $factory;
    }

    /**
     * Indicate that ULIDs should be created normally and not using a custom factory.
     *
     * @return void
     */
    public static function createUlidsNormally()
    {
        static::$ulidFactory = null;
    }

    /**
     * Generate a new ULID.
     */
    public static function generate(): string
    {
        return static::$ulidFactory
            ? call_user_func(static::$ulidFactory)
            : strtolower((string) Str::ulid());
    }
}
