SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

{{ $cron->expression }} {{ $cron->user }} {{ $cron->command }} > {{ $cron->logPath() }} 2>&1
