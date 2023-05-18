<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Laravel\Jetstream\Features;
use Tests\DuskTestCase;

class CreateApiTokenTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_api_tokens_can_be_created(): void
    {
        if (! Features::hasApiFeatures()) {
            $this->markTestSkipped('API support is not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $browser->loginAs($user)
                ->visit('/user/api-tokens')
                ->waitForText('Create API Token')
                ->type('name', 'Test Token')
                ->check('permissions[]', 'read')
                ->check('permissions[]', 'update')
                ->press('Create')
                ->waitForText('Please copy your new API token.');

            $this->assertCount(1, $user->fresh()->tokens);
            $this->assertEquals('Test Token', $user->fresh()->tokens->first()->name);
            $this->assertTrue($user->fresh()->tokens->first()->can('read'));
            $this->assertFalse($user->fresh()->tokens->first()->can('delete'));
        });
    }
}
