<x-task-shell-defaults />

cat <<EOF >>@if($root) /root/.ssh/authorized_keys @else /home/{{ $server->username }}/.ssh/authorized_keys @endif

{{ $publicKey }}
EOF
