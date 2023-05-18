<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Laravel\Jetstream\Features;
use Tests\DuskTestCase;

class ApiTokenPermissionsTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_api_token_permissions_can_be_updated(): void
    {
        if (! Features::hasApiFeatures()) {
            $this->markTestSkipped('API support is not enabled.');

            return;
        }

        $this->browse(function (Browser $browser) {
            $user = User::factory()->withPersonalTeam()->create();

            $token = $user->tokens()->create([
                'name' => 'Test Token',
                'token' => Str::random(40),
                'abilities' => ['create', 'read'],
            ]);

            $browser->loginAs($user)
                ->visit('/user/api-tokens')
                ->waitForText('Manage API Tokens')
                ->clickLink('Permissions')
                ->waitForText('API Token Permissions')
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser->uncheck('permissions[]', 'create')
                        ->uncheck('permissions[]', 'read')
                        ->check('permissions[]', 'delete')
                        ->press('Save');
                })
                ->waitUntilMissingText('API Token Permissions');

            $this->assertTrue($user->fresh()->tokens->first()->can('delete'));
            $this->assertFalse($user->fresh()->tokens->first()->can('read'));
            $this->assertFalse($user->fresh()->tokens->first()->can('missing-permission'));
        });
    }
}
