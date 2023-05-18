@include('tasks.apt-functions')

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

@include('tasks.software.update-php-config', ['version' => '8.1'])

service php8.1-fpm restart > /dev/null 2>&1

echo "{!! $server->username !!} ALL=NOPASSWD: /usr/sbin/service php8.1-fpm reload" >> /etc/sudoers.d/php-fpm
