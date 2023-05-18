<?php

namespace Tests\Browser;

use App\Models\User;
use App\Provider;
use Database\Factories\CredentialsFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

class CredentialsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_add_digital_ocean_credentials()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $this->assertEquals(0, $user->credentials()->count());

            $browser
                ->loginAs($user)
                ->visit('/credentials')
                ->clickLink('Add Credentials')
                ->waitForModal()
                ->select('provider', 'digital_ocean')
                ->type('name', 'My Digital Ocean Credentials')
                ->type('credentials.digital_ocean_token', 'valid-token')
                ->press('Submit')
                ->waitForText('Credentials added.');

            $this->assertCount(1, $user->credentials);

            $this->assertEquals(Provider::DigitalOcean, $user->credentials->first()->provider);
            $this->assertTrue($user->currentTeam->is($user->credentials->first()->team));
            $this->assertEquals('My Digital Ocean Credentials', $user->credentials->first()->name);
            $this->assertEquals('valid-token', $user->credentials->first()->credentials['digital_ocean_token']);
        });
    }

    /** @test */
    public function it_can_add_credentials_without_binding_them_to_a_team()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $this->assertEquals(0, $user->credentials()->count());

            $browser
                ->loginAs($user)
                ->visit('/credentials')
                ->clickLink('Add Credentials')
                ->waitForModal()
                ->select('provider', 'digital_ocean')
                ->type('name', 'My Digital Ocean Credentials')
                ->uncheck('bind_to_team')
                ->type('credentials.digital_ocean_token', 'valid-token')
                ->press('Submit')
                ->waitForText('Credentials added.');

            $this->assertCount(1, $user->credentials);

            $this->assertEquals(Provider::DigitalOcean, $user->credentials->first()->provider);
            $this->assertNull($user->credentials->first()->team);
            $this->assertEquals('My Digital Ocean Credentials', $user->credentials->first()->name);
            $this->assertEquals('valid-token', $user->credentials->first()->credentials['digital_ocean_token']);
        });
    }

    /** @test */
    public function it_validates_the_digital_ocean_token()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $this->assertEquals(0, $user->credentials()->count());

            $browser
                ->loginAs($user)
                ->visit('/credentials')
                ->clickLink('Add Credentials')
                ->waitForModal()
                ->select('provider', 'digital_ocean')
                ->type('name', 'My Digital Ocean Credentials')
                ->type('credentials.digital_ocean_token', 'invalid-token')
                ->press('Submit')
                ->waitForText('The API token is invalid.');

            $this->assertEquals(0, $user->credentials()->count());
        });
    }

    /** @test */
    public function it_can_add_hetzner_cloud_credentials()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $this->assertEquals(0, $user->credentials()->count());

            $browser
                ->loginAs($user)
                ->visit('/credentials')
                ->clickLink('Add Credentials')
                ->waitForModal()
                ->select('provider', 'hetzner_cloud')
                ->type('name', 'My Hetnzer Cloud Credentials')
                ->type('credentials.hetzner_cloud_token', 'valid-token')
                ->press('Submit')
                ->waitForText('Credentials added.');

            $this->assertCount(1, $user->credentials);

            $this->assertEquals(Provider::HetznerCloud, $user->credentials->first()->provider);
            $this->assertTrue($user->currentTeam->is($user->credentials->first()->team));
            $this->assertEquals('My Hetnzer Cloud Credentials', $user->credentials->first()->name);
            $this->assertEquals('valid-token', $user->credentials->first()->credentials['hetzner_cloud_token']);
        });
    }

    /** @test */
    public function it_validates_the_hetzner_cloud_token()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            $this->assertEquals(0, $user->credentials()->count());

            $browser
                ->loginAs($user)
                ->visit('/credentials')
                ->clickLink('Add Credentials')
                ->waitForModal()
                ->select('provider', 'hetzner_cloud')
                ->type('name', 'My Hetnzer Cloud Credentials')
                ->type('credentials.hetzner_cloud_token', 'invalid-token')
                ->press('Submit')
                ->waitForText('The API token is invalid.');

            $this->assertEquals(0, $user->credentials()->count());
        });
    }

    /** @test */
    public function it_can_update_the_name_without_changing_the_token()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            /** @var Credentials */
            $credentials = CredentialsFactory::new()->digitalOcean()->forUser($user)->create();

            $browser
                ->loginAs($user)
                ->visit('/credentials')
                ->clickLink($credentials->name, 'td')
                ->waitForModal()
                ->type('name', 'My Updated Digital Ocean Credentials')
                ->assertInputValue('credentials.digital_ocean_token', '')
                ->press('Save')
                ->waitForText('Credentials updated.');

            $this->assertEquals('My Updated Digital Ocean Credentials', $credentials->fresh()->name);
        });
    }

    /** @test */
    public function it_can_update_the_token()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            /** @var Credentials */
            $credentials = CredentialsFactory::new()->digitalOcean()->forUser($user)->create();

            $browser
                ->loginAs($user)
                ->visit('/credentials')
                ->clickLink($credentials->name, 'td')
                ->waitForModal()
                ->type('credentials.digital_ocean_token', 'valid-token-2')
                ->press('Save')
                ->waitForText('Credentials updated.');

            $this->assertEquals('valid-token-2', $credentials->fresh()->credentials['digital_ocean_token']);
        });
    }

    /** @test */
    public function it_can_delete_the_credentials()
    {
        $this->browse(function (Browser $browser) {
            /** @var User */
            $user = UserFactory::new()->withPersonalTeam()->create();

            /** @var Credentials */
            $credentials = CredentialsFactory::new()->digitalOcean()->forUser($user)->create();

            $browser
                ->loginAs($user)
                ->visit('/credentials')
                ->clickLink($credentials->name, 'td')
                ->waitForModal()
                ->press('Delete Credentials')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('Credentials deleted.')
                ->assertRouteIs('credentials.index');

            $this->assertNull($credentials->fresh());
        });
    }
}
