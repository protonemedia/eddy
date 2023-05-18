<?php

namespace Tests\Browser;

use App\Jobs\InstallFirewallRule;
use App\Jobs\UninstallFirewallRule;
use App\Models\FirewallRule;
use App\Server\Firewall\RuleAction;
use Database\Factories\FirewallRuleFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use ProtoneMedia\LaravelDuskFakes\Bus\PersistentBus;
use Tests\DuskTestCase;
use Tests\ServerTest;

class FirewallRuleTest extends DuskTestCase
{
    use DatabaseMigrations;
    use PersistentBus;
    use ServerTest;

    /** @test */
    public function it_can_add_a_firewall_rule()
    {
        $this->browse(function (Browser $browser) {
            $this->assertEquals(0, $this->server->firewallRules()->count());

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.firewall-rules.index', $this->server))
                ->clickLink('Add Firewall Rule')
                ->waitForModal()
                ->type('name', 'Websockets')
                ->radio('action', RuleAction::Allow->value)
                ->type('port', 6001)
                ->type('from_ipv4', '1.2.3.4')
                ->press('Deploy')
                ->waitForText('The Firewall Rule has been created');

            $this->assertCount(1, $this->server->firewallRules);

            Bus::assertDispatched(InstallFirewallRule::class, function (InstallFirewallRule $job) {
                return $job->rule->is($this->server->firewallRules->first());
            });

            $this->assertEquals('Websockets', $this->server->firewallRules->first()->name);
            $this->assertEquals(RuleAction::Allow, $this->server->firewallRules->first()->action);
            $this->assertEquals(6001, $this->server->firewallRules->first()->port);
            $this->assertEquals('1.2.3.4', $this->server->firewallRules->first()->from_ipv4);
        });
    }

    /** @test */
    public function it_can_edit_the_name_of_an_existing_firewall_rule()
    {
        $this->browse(function (Browser $browser) {
            /** @var FirewallRule */
            $firewallRule = FirewallRuleFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.firewall-rules.index', $this->server))
                ->click('tbody td')
                ->waitForModal()
                ->type('name', 'Updated Name')
                ->press('Save')
                ->waitForText('The Firewall Rule name has been updated')
                ->assertRouteIs('servers.firewall-rules.index', $this->server);

            $this->assertEquals('Updated Name', $firewallRule->fresh()->name);
        });
    }

    /** @test */
    public function it_can_delete_an_existing_firewall_rule()
    {
        $this->browse(function (Browser $browser) {
            /** @var FirewallRule */
            $firewallRule = FirewallRuleFactory::new()->forServer($this->server)->create();

            $browser
                ->loginAs($this->user)
                ->visit(route('servers.firewall-rules.index', $this->server))
                ->click('tbody td')
                ->waitForModal()
                ->press('Delete Firewall Rule')
                ->waitForText('Are you sure you want to continue?')
                ->press('@splade-confirm-confirm')
                ->waitForText('The Firewall Rule will be uninstalled')
                ->assertRouteIs('servers.firewall-rules.index', $this->server);

            $this->assertNotNull($firewallRule->fresh()->uninstallation_requested_at);

            Bus::assertDispatched(UninstallFirewallRule::class, function (UninstallFirewallRule $job) use ($firewallRule) {
                return $job->rule->is($firewallRule);
            });
        });
    }
}
