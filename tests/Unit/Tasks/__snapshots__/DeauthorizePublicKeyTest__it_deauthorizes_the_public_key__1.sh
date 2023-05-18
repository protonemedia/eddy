set -eu
export DEBIAN_FRONTEND=noninteractive

ssh-keygen -f /home/eddy/.ssh/authorized_keys -R "public-key"