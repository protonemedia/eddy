<x-task-shell-defaults />

ssh-keygen -f /home/{{ $server->username }}/.ssh/authorized_keys -R "{{ $publicKey }}"