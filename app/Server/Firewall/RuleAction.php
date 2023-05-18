<?php

namespace App\Server\Firewall;

enum RuleAction: string
{
    case Allow = 'allow';
    case Deny = 'deny';
    case Reject = 'reject';
}
