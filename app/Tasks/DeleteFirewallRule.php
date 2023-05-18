<?php

namespace App\Tasks;

use App\Models\FirewallRule;

class DeleteFirewallRule extends Task
{
    public function __construct(public FirewallRule $rule)
    {
    }
}
