<?php

namespace App\SourceControl;

use App\Models\Credentials;
use App\Provider;
use Exception;

class ProviderFactory
{
    public function forCredentials(Credentials $credentials): mixed
    {
        return match ($credentials->provider) {
            Provider::Github => new Github($credentials->credentials['token']),

            default => throw new Exception('Invalid provider')
        };
    }
}
