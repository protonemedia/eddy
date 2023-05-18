@include('tasks.apt-functions')

echo "Install Caddy webserver"

waitForAptUnlock
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list
waitForAptUnlock
apt-get update
waitForAptUnlock
apt-get install -y caddy=2.*

echo "Install default Caddyfile"

cat > /etc/caddy/Sites.caddy << EOF
# import /home/{{ $server->username }}/example.com/Caddyfile
EOF

cat > /etc/caddy/Caddyfile << EOF
{!! $server->public_ipv4 !!}:80 {
    root * /home/{!! $server->username !!}/default
    file_server
}

# Do not remove this Sites.caddy import
import /etc/caddy/Sites.caddy
EOF

echo "Update Caddy service config to run as user"

service caddy stop
mkdir -p /etc/systemd/system/caddy.service.d

cat > /etc/systemd/system/caddy.service.d/override.conf << EOF
[Service]
User={!! $server->username !!}
Group={!! $server->username !!}

EOF

systemctl daemon-reload
service caddy start

echo "{!! $server->username !!} ALL=(root) NOPASSWD: /usr/sbin/service caddy reload" >> /etc/sudoers.d/caddy
