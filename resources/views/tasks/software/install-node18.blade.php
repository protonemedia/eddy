@include('tasks.apt-functions')

echo "Install Node 18"

waitForAptUnlock
curl --silent --location https://deb.nodesource.com/setup_18.x | bash -
apt-get update
waitForAptUnlock

apt-get install -y --force-yes nodejs

echo "Install Node Packages"

npm install -g fx gulp n pm2 svgo yarn zx