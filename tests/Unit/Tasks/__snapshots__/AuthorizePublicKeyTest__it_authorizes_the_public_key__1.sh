set -eu
export DEBIAN_FRONTEND=noninteractive

cat <<EOF >> /home/eddy/.ssh/authorized_keys
public-key
EOF
