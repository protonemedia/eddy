echo "Add public key for this server"

mkdir -p /root/.ssh
touch /root/.ssh/authorized_keys

cat <<EOF >> /root/.ssh/authorized_keys
{{ $server->public_key }}
EOF

echo "Fix root permissions"

chown root:root /root
chown -R root:root /root/.ssh
chmod 700 /root/.ssh
chmod 600 /root/.ssh/authorized_keys

echo "SSH Keyscans for Source Providers"

ssh-keyscan -H github.com >> /root/.ssh/known_hosts
ssh-keyscan -H bitbucket.org >> /root/.ssh/known_hosts
ssh-keyscan -H gitlab.com >> /root/.ssh/known_hosts


