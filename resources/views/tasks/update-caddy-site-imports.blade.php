# Update Caddy site imports

cat > /etc/caddy/Sites.caddy << EOF
# import /home/eddy/example.com/Caddyfile

@foreach($sites() as $site)
import {!! $site->path !!}/Caddyfile

@endforeach

EOF

# Reload Caddy
/usr/sbin/service caddy reload
