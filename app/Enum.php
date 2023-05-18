<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum as EnumRule;

class Enum
{
    public static function options(array|string $cases, bool $sort = false): array
    {
        if (is_string($cases)) {
            $cases = $cases::cases();
        }

        return Collection::make($cases)
            ->mapWithKeys(function (object $item) {
                return [$item->value => $item->name];
            })
            ->when($sort, fn (Collection $options) => $options->sort())
            ->all();
    }

    public static function values(array|string $cases): array
    {
        return Collection::make(static::options($cases))->keys()->all();
    }

    public static function rule(string $type): EnumRule
    {
        return new EnumRule($type);
    }

    public static function requiredIf(object $item, string $field = null): string
    {
        $field = $field ?: Str::snake(class_basename($item));

        return sprintf('required_if:%s,%s', $field, $item->value);
    }

    public static function requiredUnless(object $item, string $field = null): string
    {
        $field = $field ?: Str::snake(class_basename($item));

        return sprintf('required_unless:%s,%s', $field, $item->value);
    }
}
