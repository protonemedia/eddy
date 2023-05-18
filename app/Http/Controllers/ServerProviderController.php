<?php

namespace App\Http\Controllers;

use App\Infrastructure\Entities\Image;
use App\Infrastructure\Entities\OperatingSystem;
use App\Infrastructure\Entities\Region;
use App\Infrastructure\Entities\ServerType;
use App\Infrastructure\ProviderFactory;
use App\Infrastructure\ServerProvider;
use App\Models\Credentials;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class ServerProviderController extends Controller
{
    public function __construct(private ProviderFactory $providerFactory)
    {
    }

    public function regions(Credentials $credentials)
    {
        /** @var ServerProvider */
        $provider = $this->providerFactory->forCredentials($credentials);

        return $provider->findAvailableServerRegions()->mapWithKeys(function (Region $region) {
            return [$region->id => $region->name];
        });
    }

    public function types(Credentials $credentials, $region)
    {
        /** @var ServerProvider */
        $provider = $this->providerFactory->forCredentials($credentials);

        return $provider->findAvailableServerTypesByRegion($region)
            ->sortBy(function (ServerType $serverType) {
                return $serverType->monthlyPriceAmount;
            })
            ->mapWithKeys(function (ServerType $serverType) {
                return [$serverType->id => $serverType->name];
            });
    }

    public function images(Credentials $credentials, $region)
    {
        /** @var ServerProvider */
        $provider = $this->providerFactory->forCredentials($credentials);

        return $provider->findAvailableServerImagesByRegion($region)
            ->filter(function (Image $image) {
                return $image->operatingSystem === OperatingSystem::Ubuntu2204;
            })
            ->mapWithKeys(function (Image $image) {
                return [$image->id => 'Ubuntu 22.04'];
            });
    }
}
