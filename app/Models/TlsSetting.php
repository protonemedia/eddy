<?php

namespace App\Models;

enum TlsSetting: string
{
    case Auto = 'auto';
    case Custom = 'custom';
    case Internal = 'internal';
    case Off = 'off';

    public function getDisplayName(): string
    {
        return $this->name;
    }
}
