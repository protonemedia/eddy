# Update Caddy site imports

cat > /etc/caddy/Sites.caddy << EOF
# import /home/eddy/example.com/Caddyfile

import /home/eddy/google.com/Caddyfile

import /home/eddy/apple.com/Caddyfile

EOF

# Reload Caddy
/usr/sbin/service caddy reload
