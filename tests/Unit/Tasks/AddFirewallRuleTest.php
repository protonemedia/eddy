<?php

namespace Tests\Unit\Tasks;

use App\Models\FirewallRule;
use App\Tasks\AddFirewallRule;
use Database\Factories\FirewallRuleFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddFirewallRuleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_executes_the_formatted_command()
    {
        /** @var FirewallRule */
        $rule = FirewallRuleFactory::new()->create();

        $this->assertMatchesBashSnapshot(
            (new AddFirewallRule($rule))->getScript()
        );
    }
}
