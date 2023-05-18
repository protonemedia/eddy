@include('tasks.apt-functions')

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

@include('tasks.software.update-php-config', ['version' => '8.2'])

service php8.2-fpm restart > /dev/null 2>&1

echo "{!! $server->username !!} ALL=NOPASSWD: /usr/sbin/service php8.2-fpm reload" >> /etc/sudoers.d/php-fpm
