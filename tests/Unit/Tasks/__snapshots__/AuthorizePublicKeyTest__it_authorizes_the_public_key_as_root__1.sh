set -eu
export DEBIAN_FRONTEND=noninteractive

cat <<EOF >> /root/.ssh/authorized_keys
public-key
EOF
