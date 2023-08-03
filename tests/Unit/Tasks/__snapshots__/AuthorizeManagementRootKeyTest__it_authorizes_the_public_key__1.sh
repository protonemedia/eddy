set -eu
export DEBIAN_FRONTEND=noninteractive

echo "Setup SSH keys for root"

if [ ! -d /root/.ssh ]
then
    mkdir -p /root/.ssh
    touch /root/.ssh/authorized_keys
fi

cat <<EOF >> /root/.ssh/authorized_keys

ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIAb5M7vlstlBOPx6NocXAewxzfxX8AujDifR0lrQf+On eddy@protone.media
EOF

echo "Fix root permissions"

chown root:root /root
chown -R root:root /root/.ssh
chmod 700 /root/.ssh
chmod 600 /root/.ssh/authorized_keys