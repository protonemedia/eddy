<x-task-shell-defaults />

chown -R {!! $server->username !!}:{!! $server->username !!} /home/{!! $server->username !!}/.config/composer

runuser -l {!! $server->username !!} -c 'composer global require protonemedia/eddy-backup-cli'