<x-task-shell-defaults />

ssh-keygen -t ed25519 -C "{{ $comment() }}" -f {{ $privatePath }} -N ""