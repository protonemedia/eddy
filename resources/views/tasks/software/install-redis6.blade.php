@include('tasks.apt-functions')

echo "Install Redis"

waitForAptUnlock
apt-get install -y redis-server=5:6*
sed -i 's/bind 127.0.0.1/bind 0.0.0.0/' /etc/redis/redis.conf
service redis-server restart
systemctl enable redis-server