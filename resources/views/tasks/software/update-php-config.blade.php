# https://github.com/deployphp/deployer/blob/master/recipe/provision/php.php

echo "Update PHP CLI config"

sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php/{{ $version }}/cli/php.ini
sed -i "s/display_errors = .*/display_errors = On/" /etc/php/{{ $version }}/cli/php.ini
sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/{{ $version }}/cli/php.ini
sed -i "s/;date.timezone.*/date.timezone = UTC/" /etc/php/{{ $version }}/cli/php.ini
sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php/{{ $version }}/cli/php.ini

echo "Update PHP FPM config"

sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php/{{ $version }}/fpm/php.ini
sed -i "s/display_errors = .*/display_errors = Off/" /etc/php/{{ $version }}/fpm/php.ini
sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/{{ $version }}/fpm/php.ini
sed -i "s/;date.timezone.*/date.timezone = UTC/" /etc/php/{{ $version }}/fpm/php.ini
sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php/{{ $version }}/fpm/php.ini

echo "Update PHP FPM pool config"

sed -i "s/;request_terminate_timeout.*/request_terminate_timeout = 60/" /etc/php/{{ $version }}/fpm/pool.d/www.conf
sed -i "s/^user = www-data/user = {!! $server->username !!}/" /etc/php/{{ $version }}/fpm/pool.d/www.conf
sed -i "s/^group = www-data/group = {!! $server->username !!}/" /etc/php/{{ $version }}/fpm/pool.d/www.conf
sed -i "s/;listen\.owner.*/listen.owner = {!! $server->username !!}/" /etc/php/{{ $version }}/fpm/pool.d/www.conf
sed -i "s/;listen\.group.*/listen.group = {!! $server->username !!}/" /etc/php/{{ $version }}/fpm/pool.d/www.conf
sed -i "s/;listen\.mode.*/listen.mode = 0666/" /etc/php/{{ $version }}/fpm/pool.d/www.conf
sed -i "s/^pm.max_children.*=.*/pm.max_children = {{ $maxChildrenPhpPool() }}/" /etc/php/{{ $version }}/fpm/pool.d/www.conf

echo "Update PHP session config"

#
chmod 733 /var/lib/php/sessions

# Set the sticky bit on the directory to prevent other users from deleting the session files
chmod +t /var/lib/php/sessions