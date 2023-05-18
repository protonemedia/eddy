<x-task-shell-defaults />

@if($rule->action === \App\Server\Firewall\RuleAction::Allow)
    ufw {!! $rule->formatAsUfwRule() !!}
@else
    ufw insert 1 {!! $rule->formatAsUfwRule() !!}
@endif
