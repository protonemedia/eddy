set -eu
export DEBIAN_FRONTEND=noninteractive

export PHP_BINARY=/usr/bin/php8.1
# Create the necessary directories
mkdir -p /home/protone/app.com/repository
mkdir -p /home/protone/app.com/logs

# Check if the repository exists and if the remote URL is correct, if not, delete it
if [ -f "/home/protone/app.com/repository/HEAD" ]; then
    cd /home/protone/app.com/repository
    CURRENT_REMOTE_URL=$(git config --get remote.origin.url || echo '');

    if [ "$CURRENT_REMOTE_URL" != 'git@github.com:protonemedia/php-app.dev.git' ]; then
        git remote set-url origin git@github.com:protonemedia/php-app.dev.git

    fi
fi

# Store the deploy key and set the GIT SSH command
cat <<EOF >> /home/protone/app.com/deploy_key
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW
QyNTUxOQAAACAG+TO75bLZQTj8ejaHFwHsMc38V/ALow4n0dJa0H/jpwAAAJi5D4sRuQ+L
EQAAAAtzc2gtZWQyNTUxOQAAACAG+TO75bLZQTj8ejaHFwHsMc38V/ALow4n0dJa0H/jpw
AAAECfx82V4HmzOrhOL/7//ZjRt8Az4CDGEKFVf87GwAAyUAb5M7vlstlBOPx6NocXAewx
zfxX8AujDifR0lrQf+OnAAAAE2NhZGR5QHByb3RvbmUubWVkaWEBAg==
-----END OPENSSH PRIVATE KEY-----

EOF

chmod 600 /home/protone/app.com/deploy_key
export GIT_SSH_COMMAND="ssh -i /home/protone/app.com/deploy_key"

cd /home/protone/app.com

# Clone the repository if it doesn't exist
if [ ! -f "/home/protone/app.com/repository/.git/HEAD" ]; then
    git clone git@github.com:protonemedia/php-app.dev.git /home/protone/app.com/repository
fi

# Fetch the latest changes from the repository
cd /home/protone/app.com/repository

git pull origin main

cd /home/protone/app.com

cd /home/protone/app.com/repository

GIT_HASH=$(git rev-list main -1);

httpPostRawSilently https://webhook.app/webhook/task/id/callback?signature=5cc2858bad90edbea169a383b9b0246e777a21c6f2729e95928ad738658c9e81 "git_hash=$GIT_HASH"

cd /home/protone/app.com

cd /home/protone/app.com

echo "Done!"