set -eu
export DEBIAN_FRONTEND=noninteractive

export PHP_BINARY=/usr/bin/php8.1
# Create the necessary directories
mkdir -p /home/protone/app.com/repository
mkdir -p /home/protone/app.com/shared
mkdir -p /home/protone/app.com/releases/1641038400
mkdir -p /home/protone/app.com/logs

# Cleanup old releases
DEPLOYMENT_KEEP="none"

# Get a list of all deployments, sorted by timestamp in ascending order
DEPLOYMENT_LIST=($(ls -1 /home/protone/app.com/releases | sort -n))

# Determine how many deployments to delete
NUM_TO_DELETE=$((${#DEPLOYMENT_LIST[@]} - 10))

# Loop through the deployments to delete
for ((i=0; i<$NUM_TO_DELETE; i++)); do
    DEPLOY=${DEPLOYMENT_LIST[$i]}
    # Skip the deployment to keep
    if [[ $DEPLOY == $DEPLOYMENT_KEEP ]]; then
        continue
    fi

    # Delete the deployment
    rm -rf /home/protone/app.com/releases/$DEPLOY
done

# Check if the repository exists and if the remote URL is correct, if not, delete it
if [ -f "/home/protone/app.com/repository/HEAD" ]; then
    cd /home/protone/app.com/repository
    CURRENT_REMOTE_URL=$(git config --get remote.origin.url || echo '');

    if [ "$CURRENT_REMOTE_URL" != 'git@github.com:protonemedia/php-app.dev.git' ]; then
        rm -rf /home/protone/app.com/repository
        cd /home/protone/app.com
        mkdir -p /home/protone/app.com/repository

    fi
fi

cd /home/protone/app.com

# Clone the repository if it doesn't exist
if [ ! -f "/home/protone/app.com/repository/HEAD" ]; then
    git clone --mirror git@github.com:protonemedia/php-app.dev.git /home/protone/app.com/repository
fi

# Fetch the latest changes from the repository
cd /home/protone/app.com/repository

git remote update

# Clone the repository into the release directory
cd /home/protone/app.com/releases/1641038400
git clone -l /home/protone/app.com/repository .
git checkout --force main

cd /home/protone/app.com

cd /home/protone/app.com

cd /home/protone/app.com

if [ ! -d "/home/protone/app.com/shared/storage" ]; then
    # Create shared directory if it does not exist.
    mkdir -p /home/protone/app.com/shared/storage

    if [ -d "/home/protone/app.com/releases/1641038400/storage" ]; then
        # Copy contents of release directory to shared directory if it exists.
        cp -r /home/protone/app.com/releases/1641038400/storage /home/protone/app.com/shared/.
    fi
fi

#  Remove shared directory from release directory if it exists.
rm -rf /home/protone/app.com/releases/1641038400/storage

# Create parent directory of shared directory in release directory if it does not exist,
# otherwise symlink will fail.
mkdir -p `dirname /home/protone/app.com/releases/1641038400/storage`

# Symlink shared directory to release directory.
ln -nfs --relative /home/protone/app.com/shared/storage /home/protone/app.com/releases/1641038400/storage

# Create directories in shared and release directories if they don't exist
mkdir -p /home/protone/app.com/releases/1641038400/.
mkdir -p /home/protone/app.com/shared/.

# If the shared file does not exist, but the release file does, copy the release file to shared
if [ ! -f "/home/protone/app.com/shared/.env" ] && [ -f "/home/protone/app.com/releases/1641038400/.env" ]; then
    cp /home/protone/app.com/releases/1641038400/.env /home/protone/app.com/shared/.env
fi

# If the shared file still does not exist, create it
if [ ! -f "/home/protone/app.com/shared/.env" ]; then
    touch /home/protone/app.com/shared/.env
fi

# If the release file exists, remove it
if [ -f "/home/protone/app.com/releases/1641038400/.env" ]; then
    rm -rf /home/protone/app.com/releases/1641038400/.env
fi

# Create symlink
ln -nfs --relative /home/protone/app.com/shared/.env /home/protone/app.com/releases/1641038400/.env

DIRECTORY_IS_WRITEABLE=$(getfacl -p /home/protone/app.com/releases/1641038400/bootstrap/cache | grep "^user:protone:.*w" | wc -l)

if [ $DIRECTORY_IS_WRITEABLE -eq 0 ]; then
    # Make the directory writable (without sudo)
    setfacl -L -m u:protone:rwX /home/protone/app.com/releases/1641038400/bootstrap/cache
    setfacl -dL -m u:protone:rwX /home/protone/app.com/releases/1641038400/bootstrap/cache
fi

cd /home/protone/app.com
ln -nfs --relative /home/protone/app.com/releases/1641038400 /home/protone/app.com/current

cd /home/protone/app.com/repository

GIT_HASH=$(git rev-list main -1);

httpPostRawSilently https://webhook.app/webhook/task/id/callback?signature=5cc2858bad90edbea169a383b9b0246e777a21c6f2729e95928ad738658c9e81 "git_hash=$GIT_HASH"

echo "Done!"