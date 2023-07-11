SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

{{ $backup->cron_expression }} {{ $backup->server->username }} {{ $backup->cronCommand() }} 2>&1
