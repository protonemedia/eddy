<?php

namespace Tests\Browser\Jetstream;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Laravel\Jetstream\Features;
use Tests\DuskTestCase;

class DeleteApiTokenTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_api_tokens_can_be_deleted(): void
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
                ->press('Delete')
                ->within('#headlessui-portal-root', function (Browser $browser) {
                    $browser->type('input', 'password')
                        ->press('Delete');
                })
                ->waitUntilMissingText('Manage API Tokens');

            $this->assertCount(0, $user->fresh()->tokens);
        });
    }
}
