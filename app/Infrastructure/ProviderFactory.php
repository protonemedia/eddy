<?php

namespace App\Infrastructure;

use App\Models\Credentials;
use App\Models\Server;
use App\Provider;
use Exception;
use ProtoneMedia\LaravelTaskRunner\ProcessRunner;

class ProviderFactory
{
    public function __construct(private ProcessRunner $processRunner)
    {
    }

    public function forServer(Server $server): ServerProvider
    {
        if ($server->credentials) {
            return $this->forCredentials($server->credentials);
        }

        return match ($server->provider) {
            Provider::Vagrant => new Vagrant($this->processRunner, config('services.vagrant.path')),

            default => throw new Exception('Invalid provider')
        };
    }

    public function forCredentials(Credentials $credentials): mixed
    {
        return match ($credentials->provider) {
            Provider::DigitalOcean => new DigitalOcean($credentials->credentials['digital_ocean_token']),
            Provider::HetznerCloud => new HetznerCloud($credentials->credentials['hetzner_cloud_token']),
            Provider::Vagrant => new Vagrant($this->processRunner, config('services.vagrant.path')),

            default => throw new Exception('Invalid provider')
        };
    }
}
