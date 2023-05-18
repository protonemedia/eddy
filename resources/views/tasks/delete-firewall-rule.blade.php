<x-task-shell-defaults />

ufw delete {!! $rule->formatAsUfwRule() !!}