<?php

namespace Tests\Unit\Models;

use App\Models\Credentials;
use App\Models\Team;
use App\Provider;
use Database\Factories\CredentialsFactory;
use Database\Factories\TeamFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;

class CredentialsTest extends TestCase
{
    /** @test */
    public function it_can_return_provider_name_attribute()
    {
        $credentials = Credentials::factory()->create([
            'provider' => $provider = Provider::DigitalOcean,
        ]);

        $this->assertEquals('DigitalOcean', $credentials->provider_name);
    }

    /** @test */
    public function it_can_return_name_without_provider_attribute_if_the_provider_is_already_in_the_name()
    {
        $credentials = Credentials::factory()->create([
            'name' => 'DigitalOcean Credentials',
            'provider' => $provider = Provider::DigitalOcean,
        ]);

        $this->assertEquals('DigitalOcean Credentials', $credentials->name_with_provider);
    }

    /** @test */
    public function it_can_return_name_with_provider_attribute_if_the_provider_is_not_in_the_name()
    {
        $credentials = Credentials::factory()->create([
            'name' => 'Test Credentials',
            'provider' => $provider = Provider::DigitalOcean,
        ]);

        $this->assertEquals('Test Credentials (DigitalOcean)', $credentials->name_with_provider);
    }

    /** @test */
    public function it_knows_whether_the_credentials_are_bound_to_a_team()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        /** @var Team */
        $personalTeam = $user->currentTeam;

        /** @var Team */
        $anotherTeam = TeamFactory::new()->create();
        $anotherTeam->users()->attach($user);

        /** @var Team */
        $anotherTeamWithoutUser = TeamFactory::new()->create();

        $this->assertTrue($user->fresh()->currentTeam->is($personalTeam));

        /** @var Credentials */
        $credentialsForPersonalTeam = CredentialsFactory::new()->forUser($user)->forTeam($personalTeam)->vagrant()->create();

        /** @var Credentials */
        $unboundCredentials = CredentialsFactory::new()->forUser($user)->vagrant()->create();

        $this->assertTrue($credentialsForPersonalTeam->canBeUsedByTeam($personalTeam));
        $this->assertFalse($credentialsForPersonalTeam->canBeUsedByTeam($anotherTeam));
        $this->assertFalse($credentialsForPersonalTeam->canBeUsedByTeam($anotherTeamWithoutUser));

        $this->assertTrue($unboundCredentials->canBeUsedByTeam($personalTeam));
        $this->assertTrue($unboundCredentials->canBeUsedByTeam($anotherTeam));
        $this->assertFalse($unboundCredentials->canBeUsedByTeam($anotherTeamWithoutUser));

        // With scope:
        $credentials = Credentials::query()->canBeUsedByTeam($personalTeam)->get();

        $this->assertTrue($credentials->contains($credentialsForPersonalTeam));
        $this->assertTrue($credentials->contains($unboundCredentials));

        $credentials = Credentials::query()->canBeUsedByTeam($anotherTeam)->get();

        $this->assertFalse($credentials->contains($credentialsForPersonalTeam));
        $this->assertTrue($credentials->contains($unboundCredentials));

        $credentials = Credentials::query()->canBeUsedByTeam($anotherTeamWithoutUser)->get();

        $this->assertFalse($credentials->contains($credentialsForPersonalTeam));
        $this->assertFalse($credentials->contains($unboundCredentials));
    }
}
