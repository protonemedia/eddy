set -eu
export DEBIAN_FRONTEND=noninteractive

# Send a POST request to the given URL, ignoring the response and errors
function httpPostSilently()
{
    if [ -z "${2:-}" ]; then
        (curl -X POST --silent --max-time 15 --output /dev/null $1 || true)
    else
        (curl -X POST --silent --max-time 15 --output /dev/null $1 -H 'Content-Type: application/json' --data $2 || true)
    fi
}

function httpPostRawSilently()
{
    (curl -X POST --silent --max-time 15 --output /dev/null $1 --data "$2" || true)
}

# Wait for apt to be unlocked
function waitForAptUnlock()
{
    while ps -C apt,apt-get,dpkg >/dev/null 2>&1; do
        echo "apt, apt-get or dpkg is running..."
        sleep 5
    done

    while fuser /var/{lib/{dpkg,apt/lists},cache/apt/archives}/{lock,lock-frontend} >/dev/null 2>&1; do
        echo "Waiting: apt is locked..."
        sleep 5
    done

    if [ -f /var/log/unattended-upgrades/unattended-upgrades.log ]; then
        while fuser /var/log/unattended-upgrades/unattended-upgrades.log >/dev/null 2>&1; do
            echo "Waiting: unattended-upgrades is locked..."
            sleep 5
        done
    fi
}

echo "Configure swap"

if [ ! -f /swapfile ]; then
    fallocate -l 1024M /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile || true
    echo "/swapfile none swap sw 0 0" >> /etc/fstab
    echo "vm.swappiness=20" >> /etc/sysctl.conf
    echo "vm.vfs_cache_pressure=50" >> /etc/sysctl.conf
fi
httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"provision_step_completed":"configure_swap"}'
echo "Configure firewall with SSH port, HTTP and HTTPS"

ufw allow 22
ufw allow 80
ufw allow 443
yes | ufw enable
service ufw restart
httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"provision_step_completed":"configure_firewall"}'
echo "Update package repositories and upgrade packages"

#  This ensures that the cloud-init bundle is not overwritten by a different version when running apt-get upgrade.
waitForAptUnlock
apt-mark hold cloud-init

# Update package repositories
waitForAptUnlock
apt-get update -y

# Install software-properties-common
waitForAptUnlock
apt-get install software-properties-common -y

# Add universe repository
waitForAptUnlock
add-apt-repository universe -y

# Update package repositories
waitForAptUnlock
apt-get update -y

# Upgrade packages
waitForAptUnlock
apt-get upgrade -y

httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"provision_step_completed":"apt_update_upgrade"}'
echo "Install essential packages"

apt-get install -y \
    acl \
    apt-transport-https \
    build-essential \
    ca-certificates \
    cron \
    curl \
    debian-archive-keyring \
    debian-keyring \
    fail2ban \
    g++ \
    gcc \
    gifsicle \
    git \
    gnupg \
    htop \
    iproute2 \
    jpegoptim \
    jq \
    libmagickwand-dev \
    libmcrypt4 \
    libonig-dev \
    libpcre3-dev \
    libpng-dev \
    libzip-dev \
    lsb-release \
    make \
    nano \
    ncdu \
    net-tools \
    optipng \
    pkg-config \
    pngquant \
    procps \
    python3 \
    python3-pip \
    sendmail \
    software-properties-common \
    sudo \
    supervisor \
    ufw \
    unattended-upgrades \
    unzip \
    uuid-runtime \
    vim \
    wget \
    whois \
    zip \
    zsh
httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"provision_step_completed":"install_essential_packages"}'
echo "Setup unattended upgrades"

cat > /etc/apt/apt.conf.d/50unattended-upgrades << EOF
Unattended-Upgrade::Allowed-Origins {
    "\${distro_id} \${distro_codename}-security";
};
Unattended-Upgrade::Package-Blacklist {
    //
};
EOF

cat > /etc/apt/apt.conf.d/10periodic << EOF
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Download-Upgradeable-Packages "1";
APT::Periodic::AutocleanInterval "7";
APT::Periodic::Unattended-Upgrade "1";
EOF
httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"provision_step_completed":"setup_unattended_upgrades"}'
echo "Add public key for this server"

mkdir -p /root/.ssh
touch /root/.ssh/authorized_keys

cat <<EOF >> /root/.ssh/authorized_keys
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIAb5M7vlstlBOPx6NocXAewxzfxX8AujDifR0lrQf+On eddy@protone.media
EOF

echo "Fix root permissions"

chown root:root /root
chown -R root:root /root/.ssh
chmod 700 /root/.ssh
chmod 600 /root/.ssh/authorized_keys

echo "SSH Keyscans for Source Providers"

ssh-keyscan -H github.com >> /root/.ssh/known_hosts
ssh-keyscan -H bitbucket.org >> /root/.ssh/known_hosts
ssh-keyscan -H gitlab.com >> /root/.ssh/known_hosts

httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"provision_step_completed":"setup_root"}'
echo "Enhance SSH security"

sed -i "/PasswordAuthentication yes/d" /etc/ssh/sshd_config
echo "PasswordAuthentication no" | tee -a /etc/ssh/sshd_config
service ssh restart

echo "Setup SSH keys for root"

if [ ! -d /root/.ssh ]
then
    mkdir -p /root/.ssh
    touch /root/.ssh/authorized_keys
fi

httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"provision_step_completed":"ssh_security"}'
echo "Rename existing user 1000 if it exists, otherwise create a new user"

if getent passwd 1000 > /dev/null 2>&1; then
    echo "Renaming existing user 1000"
    OLD_USERNAME=$(getent passwd 1000 | cut -d: -f1)
    (pkill -9 -u $OLD_USERNAME || true)
    (pkill -KILL -u $OLD_USERNAME || true)
    usermod --login eddy --move-home --home /home/eddy $OLD_USERNAME
    groupmod --new-name eddy $OLD_USERNAME
else
    echo "Setup default user"
    useradd eddy
fi

echo "Create the user's home directory"

mkdir -p /home/eddy/.eddy
mkdir -p /home/eddy/.ssh

echo "Add user to groups"

adduser eddy sudo
id eddy
groups eddy

echo "Set shell"

chsh -s /bin/bash eddy

echo "Init default profile/bashrc"

cp /root/.bashrc /home/eddy/.bashrc
cp /root/.profile /home/eddy/.profile

echo "Copy SSH settings from root and create new key"

cp /root/.ssh/authorized_keys /home/eddy/.ssh/authorized_keys
cp /root/.ssh/known_hosts /home/eddy/.ssh/known_hosts
ssh-keygen -f /home/eddy/.ssh/id_rsa -t rsa -N ''

echo "Set password"

PASSWORD=$(mkpasswd -m sha-512 password)
usermod --password $PASSWORD eddy

echo "Add default Caddy page"

mkdir -p /home/eddy/default
cat <<EOF >> /home/eddy/default/index.html
This server is managed by <a href="http://127.0.0.1:8000">Eddy</a>.

EOF

echo "Fix user permissions"

chown -R eddy:eddy /home/eddy
chmod -R 755 /home/eddy
chmod 700 /home/eddy/.ssh
chmod 700 /home/eddy/.ssh/id_rsa
chmod 600 /home/eddy/.ssh/authorized_keys
httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"provision_step_completed":"setup_default_user"}'

echo "Install Caddy webserver"

waitForAptUnlock
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list
waitForAptUnlock
apt-get update
waitForAptUnlock
apt-get install -y caddy=2.*

echo "Install default Caddyfile"

cat > /etc/caddy/Sites.caddy << EOF
# import /home/eddy/example.com/Caddyfile
EOF

cat > /etc/caddy/Caddyfile << EOF
:80 {
    root * /home/eddy/default
    file_server
}

# Do not remove this Sites.caddy import
import /etc/caddy/Sites.caddy
EOF

echo "Update Caddy service config to run as user"

service caddy stop
mkdir -p /etc/systemd/system/caddy.service.d

cat > /etc/systemd/system/caddy.service.d/override.conf << EOF
[Service]
User=eddy
Group=eddy

EOF

systemctl daemon-reload
service caddy start

echo "eddy ALL=(root) NOPASSWD: /usr/sbin/service caddy reload" >> /etc/sudoers.d/caddy

httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"software_installed":"caddy2"}'
echo "Install MySQL 8.0"

waitForAptUnlock

# https://dev.mysql.com/doc/mysql-apt-repo-quick-guide/en/#apt-repo-fresh-install
wget -c https://dev.mysql.com/get/mysql-apt-config_0.8.24-1_all.deb
dpkg --install mysql-apt-config_0.8.24-1_all.deb

waitForAptUnlock
apt-get update

waitForAptUnlock

debconf-set-selections <<< "mysql-community-server mysql-community-server/data-dir select ''"
debconf-set-selections <<< "mysql-community-server mysql-community-server/root-pass password password"
debconf-set-selections <<< "mysql-community-server mysql-community-server/re-root-pass password password"

# Install from mysql.com to prevent installing older 5.7.x stuff
apt-get install -y mysql-community-server
apt-get install -y mysql-server

echo "default_password_lifetime = 0" >> /etc/mysql/mysql.conf.d/mysqld.cnf

echo "" >> /etc/mysql/my.cnf
echo "[mysqld]" >> /etc/mysql/my.cnf
echo "default_authentication_plugin=mysql_native_password" >> /etc/mysql/my.cnf
echo "skip-log-bin" >> /etc/mysql/my.cnf

sed -i "s/^max_connections.*=.*/max_connections=100/" /etc/mysql/my.cnf

if grep -q "bind-address" /etc/mysql/mysql.conf.d/mysqld.cnf; then
    sed -i '/^bind-address/s/bind-address.*=.*/bind-address = */' /etc/mysql/mysql.conf.d/mysqld.cnf
else
    echo "bind-address = *" >> /etc/mysql/mysql.conf.d/mysqld.cnf
fi

mysql --user="root" --password="password" -e "CREATE USER 'root'@'' IDENTIFIED BY 'password';"
mysql --user="root" --password="password" -e "CREATE USER 'root'@'%' IDENTIFIED BY 'password';"
mysql --user="root" --password="password" -e "GRANT ALL PRIVILEGES ON *.* TO root@'' WITH GRANT OPTION;"
mysql --user="root" --password="password" -e "GRANT ALL PRIVILEGES ON *.* TO root@'%' WITH GRANT OPTION;"
service mysql restart

mysql --user="root" --password="password" -e "CREATE USER 'eddy'@'' IDENTIFIED BY 'password';"
mysql --user="root" --password="password" -e "CREATE USER 'eddy'@'%' IDENTIFIED BY 'password';"
mysql --user="root" --password="password" -e "GRANT ALL PRIVILEGES ON *.* TO 'eddy'@'' WITH GRANT OPTION;"
mysql --user="root" --password="password" -e "GRANT ALL PRIVILEGES ON *.* TO 'eddy'@'%' WITH GRANT OPTION;"
mysql --user="root" --password="password" -e "FLUSH PRIVILEGES;"

mysql --user="root" --password="password" -e "CREATE DATABASE eddy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

service mysql restart

rm mysql-apt-config_0.8.24-1_all.deb
httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"software_installed":"mysql80"}'
echo "Install Redis"

waitForAptUnlock
apt-get install -y redis-server=5:6*
sed -i 's/bind 127.0.0.1/bind 0.0.0.0/' /etc/redis/redis.conf
service redis-server restart
systemctl enable redis-server
httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"software_installed":"redis6"}'
echo "Install PHP 8.1"

waitForAptUnlock
apt-add-repository ppa:ondrej/php -y
apt-get update
waitForAptUnlock

# confdef: If a conffile has been modified and the version in the package did change,
# always choose the default action without prompting. If there is no default action
# it will stop to ask the user unless --force-confnew or --force-confold is also
# been given, in which case it will use that to decide the final action.

# confold: If a conffile has been modified and the version in the package did change,
# always keep the old version without prompting, unless the --force-confdef is also
# specified, in which case the default action is preferred.

apt-get install -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" -y --force-yes \
    php8.1-bcmath \
    php8.1-cli \
    php8.1-curl \
    php8.1-dev \
    php8.1-fpm \
    php8.1-gd \
    php8.1-gmp \
    php8.1-igbinary \
    php8.1-imap \
    php8.1-intl \
    php8.1-mbstring \
    php8.1-memcached \
    php8.1-msgpack \
    php8.1-mysql \
    php8.1-pgsql \
    php8.1-readline \
    php8.1-soap \
    php8.1-sqlite3 \
    php8.1-swoole \
    php8.1-tokenizer \
    php8.1-xml \
    php8.1-zip

echo "Install Imagick for PHP 8.1"

waitForAptUnlock
echo "extension=imagick.so" > /etc/php/8.1/mods-available/imagick.ini
yes '' | apt-get install php8.1-imagick

echo "Install Redis for PHP 8.1"

waitForAptUnlock
echo "extension=redis.so" > /etc/php/8.1/mods-available/redis.ini
yes '' | apt install php8.1-redis

# https://github.com/deployphp/deployer/blob/master/recipe/provision/php.php

echo "Update PHP CLI config"

sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php/8.1/cli/php.ini
sed -i "s/display_errors = .*/display_errors = On/" /etc/php/8.1/cli/php.ini
sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/8.1/cli/php.ini
sed -i "s/;date.timezone.*/date.timezone = UTC/" /etc/php/8.1/cli/php.ini
sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php/8.1/cli/php.ini

echo "Update PHP FPM config"

sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php/8.1/fpm/php.ini
sed -i "s/display_errors = .*/display_errors = Off/" /etc/php/8.1/fpm/php.ini
sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/8.1/fpm/php.ini
sed -i "s/;date.timezone.*/date.timezone = UTC/" /etc/php/8.1/fpm/php.ini
sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php/8.1/fpm/php.ini

echo "Update PHP FPM pool config"

sed -i "s/;request_terminate_timeout.*/request_terminate_timeout = 60/" /etc/php/8.1/fpm/pool.d/www.conf
sed -i "s/^user = www-data/user = eddy/" /etc/php/8.1/fpm/pool.d/www.conf
sed -i "s/^group = www-data/group = eddy/" /etc/php/8.1/fpm/pool.d/www.conf
sed -i "s/;listen\.owner.*/listen.owner = eddy/" /etc/php/8.1/fpm/pool.d/www.conf
sed -i "s/;listen\.group.*/listen.group = eddy/" /etc/php/8.1/fpm/pool.d/www.conf
sed -i "s/;listen\.mode.*/listen.mode = 0666/" /etc/php/8.1/fpm/pool.d/www.conf
sed -i "s/^pm.max_children.*=.*/pm.max_children = 5/" /etc/php/8.1/fpm/pool.d/www.conf

echo "Update PHP session config"

#
chmod 733 /var/lib/php/sessions

# Set the sticky bit on the directory to prevent other users from deleting the session files
chmod +t /var/lib/php/sessions
service php8.1-fpm restart > /dev/null 2>&1

echo "eddy ALL=NOPASSWD: /usr/sbin/service php8.1-fpm reload" >> /etc/sudoers.d/php-fpm

httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"software_installed":"php81"}'
echo "Install PHP 8.2"

waitForAptUnlock
apt-add-repository ppa:ondrej/php -y
apt-get update
waitForAptUnlock

# confdef: If a conffile has been modified and the version in the package did change,
# always choose the default action without prompting. If there is no default action
# it will stop to ask the user unless --force-confnew or --force-confold is also
# been given, in which case it will use that to decide the final action.

# confold: If a conffile has been modified and the version in the package did change,
# always keep the old version without prompting, unless the --force-confdef is also
# specified, in which case the default action is preferred.

apt-get install -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" -y --force-yes \
    php8.2-bcmath \
    php8.2-cli \
    php8.2-curl \
    php8.2-dev \
    php8.2-fpm \
    php8.2-gd \
    php8.2-gmp \
    php8.2-igbinary \
    php8.2-imap \
    php8.2-intl \
    php8.2-mbstring \
    php8.2-memcached \
    php8.2-msgpack \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-readline \
    php8.2-soap \
    php8.2-sqlite3 \
    php8.2-swoole \
    php8.2-tokenizer \
    php8.2-xml \
    php8.2-zip

echo "Install Imagick for PHP 8.2"

waitForAptUnlock
echo "extension=imagick.so" > /etc/php/8.2/mods-available/imagick.ini
yes '' | apt-get install php8.2-imagick

echo "Install Redis for PHP 8.2"

waitForAptUnlock
yes '' | apt-get install php8.2-redis

# https://github.com/deployphp/deployer/blob/master/recipe/provision/php.php

echo "Update PHP CLI config"

sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php/8.2/cli/php.ini
sed -i "s/display_errors = .*/display_errors = On/" /etc/php/8.2/cli/php.ini
sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/8.2/cli/php.ini
sed -i "s/;date.timezone.*/date.timezone = UTC/" /etc/php/8.2/cli/php.ini
sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php/8.2/cli/php.ini

echo "Update PHP FPM config"

sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php/8.2/fpm/php.ini
sed -i "s/display_errors = .*/display_errors = Off/" /etc/php/8.2/fpm/php.ini
sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/8.2/fpm/php.ini
sed -i "s/;date.timezone.*/date.timezone = UTC/" /etc/php/8.2/fpm/php.ini
sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php/8.2/fpm/php.ini

echo "Update PHP FPM pool config"

sed -i "s/;request_terminate_timeout.*/request_terminate_timeout = 60/" /etc/php/8.2/fpm/pool.d/www.conf
sed -i "s/^user = www-data/user = eddy/" /etc/php/8.2/fpm/pool.d/www.conf
sed -i "s/^group = www-data/group = eddy/" /etc/php/8.2/fpm/pool.d/www.conf
sed -i "s/;listen\.owner.*/listen.owner = eddy/" /etc/php/8.2/fpm/pool.d/www.conf
sed -i "s/;listen\.group.*/listen.group = eddy/" /etc/php/8.2/fpm/pool.d/www.conf
sed -i "s/;listen\.mode.*/listen.mode = 0666/" /etc/php/8.2/fpm/pool.d/www.conf
sed -i "s/^pm.max_children.*=.*/pm.max_children = 5/" /etc/php/8.2/fpm/pool.d/www.conf

echo "Update PHP session config"

#
chmod 733 /var/lib/php/sessions

# Set the sticky bit on the directory to prevent other users from deleting the session files
chmod +t /var/lib/php/sessions
service php8.2-fpm restart > /dev/null 2>&1

echo "eddy ALL=NOPASSWD: /usr/sbin/service php8.2-fpm reload" >> /etc/sudoers.d/php-fpm

httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"software_installed":"php82"}'
echo "Download and install Composer dependency manager"

curl -sS https://getcomposer.org/installer | php -- --2
mv composer.phar /usr/local/bin/composer

echo "eddy ALL=(root) NOPASSWD: /usr/local/bin/composer self-update*" > /etc/sudoers.d/composer

# Create default auth.json

mkdir -p /home/eddy/.config/composer
touch /home/eddy/.config/composer/auth.json

cat > /home/eddy/.config/composer/auth.json << 'EOF'
{
  "bearer": {},
  "bitbucket-oauth": {},
  "github-oauth": {},
  "gitlab-oauth": {},
  "gitlab-token": {},
  "http-basic": {}
}
EOF

chown -R eddy:eddy /home/eddy/.config/composer
chmod 600 /home/eddy/.config/composer/auth.json

httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"software_installed":"composer2"}'
echo "Install Node 18"

waitForAptUnlock
curl --silent --location https://deb.nodesource.com/setup_18.x | bash -
apt-get update
waitForAptUnlock

apt-get install -y --force-yes nodejs

echo "Install Node Packages"

npm install -g fx gulp n pm2 svgo yarn zx
httpPostSilently https://webhook.app/webhook/task/1/callback?signature=852aa54b2322ab416c8f5118b9cdf2f2bd623a23ba6f953e8d966ddf20ed649e '{"software_installed":"node18"}'

# See 'apt-update-upgrade'
waitForAptUnlock
apt-mark unhold cloud-init