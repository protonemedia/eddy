set -eu
export DEBIAN_FRONTEND=noninteractive

chown -R eddy:eddy /home/eddy/.config/composer

runuser -l eddy -c 'composer global require protonemedia/eddy-backup-cli'