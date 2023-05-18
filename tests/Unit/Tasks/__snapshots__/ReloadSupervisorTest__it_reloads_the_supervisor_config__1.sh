set -eu
export DEBIAN_FRONTEND=noninteractive

supervisorctl reread
supervisorctl update