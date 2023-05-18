<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Laravel\Jetstream\Jetstream;
use Tests\DuskTestCase;

class ProfileInformationTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_profile_information_can_be_updated(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Profile Information')
                ->within('@update-profile-information-form', function (Browser $browser) {
                    $browser->type('name', 'Test Name')
                        ->type('email', 'test@example.com')
                        ->press('Save')
                        ->waitForText('Saved.');
                });

            $this->assertEquals('Test Name', $user->fresh()->name);
            $this->assertEquals('test@example.com', $user->fresh()->email);
        });
    }

    public function test_profile_photo_can_be_updated_and_removed(): void
    {
        if (! Jetstream::managesProfilePhotos()) {
            $this->markTestSkipped('Profile photos are not managed by Jetstream.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/user/profile')
                ->waitForText('Profile Information')
                ->within('@update-profile-information-form', function (Browser $browser) {
                    $browser->attach('photo', __DIR__.'/photo.jpeg')
                        ->press('Save')
                        ->waitForText('Saved.');
                });

            $this->assertNotNull($user->fresh()->profile_photo_path);

            $browser->within('@update-profile-information-form', function (Browser $browser) {
                $browser->clickLink('Remove Photo')
                    ->waitUntilMissingText('Remove Photo');
            });

            $this->assertNull($user->fresh()->profile_photo_path);
        });
    }
}
