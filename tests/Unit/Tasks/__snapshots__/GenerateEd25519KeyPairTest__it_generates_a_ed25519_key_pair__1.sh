set -eu
export DEBIAN_FRONTEND=noninteractive

ssh-keygen -t ed25519 -C "eddy@protone.media" -f /tmp -N ""