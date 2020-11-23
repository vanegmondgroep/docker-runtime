[supervisord]
nodaemon = true
logfile = /dev/stderr
logfile_maxbytes = 0
pidfile = /var/run/supervisord.pid
loglevel = error

[unix_http_server]
file=/var/run/supervisor.sock
username = vanegmond
password = secret

[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[program:litespeed]
command = /usr/local/lsws/bin/openlitespeed -d
process_name = openlitespeed
autorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0

@foreach($supervisor['services'] as $service)
[program:{{ $service['name'] }}]
command = {{ $service['command'] }}
autorestart=true
stopasgroup=true
stdout_logfile = /dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile = /dev/stderr
stderr_logfile_maxbytes=0
@if(!empty($service['workdir']))
directory = {{ $service['workdir'] }}
@endif
@endforeach

[group:web]
programs = litespeed
