<x-task-shell-defaults />

echo "Setup SSH keys for root"

if [ ! -d /root/.ssh ]
then
  mkdir -p /root/.ssh
  touch /root/.ssh/authorized_keys
fi

cat <<EOF >> /root/.ssh/authorized_keys

{{ $server->public_key }}
EOF

echo "Fix root permissions"

chown root:root /root
chown -R root:root /root/.ssh
chmod 700 /root/.ssh
chmod 600 /root/.ssh/authorized_keys