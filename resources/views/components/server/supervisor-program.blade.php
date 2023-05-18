[program:{{ $daemon->id }}]
command={!! $daemon->command !!}
autostart=true
autorestart=true
startretries=5
@if($daemon->directory)
directory={!! $daemon->directory !!}
@endif
numprocs={{ $daemon->processes }}
process_name=%(program_name)s_%(process_num)02d
user={{ $daemon->user }}
startsecs=10
stopsignal={{ $daemon->stop_signal }}
stopwaitsecs={{ $daemon->stop_wait_seconds }}
stopasgroup=true
killasgroup=true
stdout_logfile={!! $daemon->outputLogPath() !!}
stderr_logfile={!! $daemon->errorLogPath() !!}
stdout_logfile_maxbytes=5MB
stderr_logfile_maxbytes=5MB