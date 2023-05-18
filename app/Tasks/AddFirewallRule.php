<?php

namespace App\Tasks;

use App\Models\FirewallRule;

class AddFirewallRule extends Task
{
    public function __construct(public FirewallRule $rule)
    {
    }
}
