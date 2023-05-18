<?php

namespace Tests\Unit\Rules;

use App\Infrastructure\HetznerCloud;
use App\Infrastructure\ProviderFactory;
use App\Models\Credentials;
use App\Provider;
use App\Rules\HetznerCloudToken;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Mockery;
use Tests\TestCase;

class HetznerCloudTokenTest extends TestCase
{
    private function mockHetznerCloud(bool $canConnect): HetznerCloud
    {
        $hetzner = Mockery::mock(HetznerCloud::class);
        $hetzner->shouldReceive('canConnect')->andReturn($canConnect);

        return $hetzner;
    }

    private function mockProviderFactory(HetznerCloud $hetzner, string $token): ProviderFactory
    {
        $providerFactory = Mockery::mock(ProviderFactory::class);
        $providerFactory->shouldReceive('forCredentials')->with(Mockery::on(function ($credentials) use ($token) {
            return $credentials instanceof Credentials
                && $credentials->provider === Provider::HetznerCloud
                && $credentials->credentials->toArray() === ['hetzner_cloud_token' => $token];
        }))->andReturn($hetzner);

        return $providerFactory;
    }

    /** @test */
    public function it_can_validate_a_token()
    {
        $hetzner = $this->mockHetznerCloud(true);
        $providerFactory = $this->mockProviderFactory($hetzner, 'valid_token');
        app()->instance(ProviderFactory::class, $providerFactory);

        $rule = new HetznerCloudToken();
        $validator = $this->makeValidator($rule, ['token' => 'valid_token']);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_can_invalidate_a_token()
    {
        $hetzner = $this->mockHetznerCloud(false);
        $providerFactory = $this->mockProviderFactory($hetzner, 'invalid_token');
        app()->instance(ProviderFactory::class, $providerFactory);

        $rule = new HetznerCloudToken();
        $validator = $this->makeValidator($rule, ['token' => 'invalid_token']);

        $this->assertTrue($validator->fails());
        $this->assertEquals('The API token is invalid.', $validator->errors()->first('token'));
    }

    private function makeValidator(HetznerCloudToken $rule, array $data): Validator
    {
        $factory = app(Factory::class);

        return $factory->make($data, ['token' => $rule]);
    }
}
