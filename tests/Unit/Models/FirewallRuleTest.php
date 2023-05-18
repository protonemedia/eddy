<?php

namespace Tests\Unit\Models;

use App\Models\FirewallRule;
use App\Server\Firewall\RuleAction;
use Tests\TestCase;

class FirewallRuleTest extends TestCase
{
    /** @test */
    public function it_can_format_the_rule_to_a_ufw_format()
    {
        $this->assertEquals(
            'allow 8000',
            (new FirewallRule(['action' => RuleAction::Allow, 'port' => 8000]))->formatAsUfwRule()
        );

        $this->assertEquals(
            'allow from 1.2.3.4 to any port 8000',
            (new FirewallRule(['action' => RuleAction::Allow, 'port' => 8000, 'from_ipv4' => '1.2.3.4']))->formatAsUfwRule()
        );

        $this->assertEquals(
            'deny 8000',
            (new FirewallRule(['action' => RuleAction::Deny, 'port' => 8000]))->formatAsUfwRule()
        );

        $this->assertEquals(
            'deny from 1.2.3.4 to any port 8000',
            (new FirewallRule(['action' => RuleAction::Deny, 'port' => 8000, 'from_ipv4' => '1.2.3.4']))->formatAsUfwRule()
        );
    }
}
