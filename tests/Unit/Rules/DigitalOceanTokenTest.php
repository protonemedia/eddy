<?php

namespace Tests\Unit\Rules;

use App\Infrastructure\DigitalOcean;
use App\Infrastructure\ProviderFactory;
use App\Models\Credentials;
use App\Provider;
use App\Rules\DigitalOceanToken;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Mockery;
use Tests\TestCase;

class DigitalOceanTokenTest extends TestCase
{
    private function mockDigitalOcean(bool $canConnect): DigitalOcean
    {
        $digitalOcean = Mockery::mock(DigitalOcean::class);
        $digitalOcean->shouldReceive('canConnect')->andReturn($canConnect);

        return $digitalOcean;
    }

    private function mockProviderFactory(DigitalOcean $digitalOcean, string $token): ProviderFactory
    {
        $providerFactory = Mockery::mock(ProviderFactory::class);
        $providerFactory->shouldReceive('forCredentials')->with(Mockery::on(function ($credentials) use ($token) {
            return $credentials instanceof Credentials
                && $credentials->provider === Provider::DigitalOcean
                && $credentials->credentials->toArray() === ['digital_ocean_token' => $token];
        }))->andReturn($digitalOcean);

        return $providerFactory;
    }

    /** @test */
    public function it_can_validate_a_token()
    {
        $digitalOcean = $this->mockDigitalOcean(true);
        $providerFactory = $this->mockProviderFactory($digitalOcean, 'valid_token');
        app()->instance(ProviderFactory::class, $providerFactory);

        $rule = new DigitalOceanToken();
        $validator = $this->makeValidator($rule, ['token' => 'valid_token']);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_can_invalidate_a_token()
    {
        $digitalOcean = $this->mockDigitalOcean(false);
        $providerFactory = $this->mockProviderFactory($digitalOcean, 'invalid_token');
        app()->instance(ProviderFactory::class, $providerFactory);

        $rule = new DigitalOceanToken();
        $validator = $this->makeValidator($rule, ['token' => 'invalid_token']);

        $this->assertTrue($validator->fails());
        $this->assertEquals('The API token is invalid.', $validator->errors()->first('token'));
    }

    private function makeValidator(DigitalOceanToken $rule, array $data): Validator
    {
        $factory = app(Factory::class);

        return $factory->make($data, ['token' => $rule]);
    }
}
