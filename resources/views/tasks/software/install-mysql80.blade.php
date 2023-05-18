@include('tasks.apt-functions')

echo "Install MySQL 8.0"

waitForAptUnlock

# https://dev.mysql.com/doc/mysql-apt-repo-quick-guide/en/#apt-repo-fresh-install
wget -c https://dev.mysql.com/get/mysql-apt-config_0.8.24-1_all.deb
dpkg --install mysql-apt-config_0.8.24-1_all.deb

waitForAptUnlock
apt-get update

waitForAptUnlock

debconf-set-selections <<< "mysql-community-server mysql-community-server/data-dir select ''"
debconf-set-selections <<< "mysql-community-server mysql-community-server/root-pass password {{ $server->database_password }}"
debconf-set-selections <<< "mysql-community-server mysql-community-server/re-root-pass password {{ $server->database_password }}"

# Install from mysql.com to prevent installing older 5.7.x stuff
apt-get install -y mysql-community-server
apt-get install -y mysql-server

echo "default_password_lifetime = 0" >> /etc/mysql/mysql.conf.d/mysqld.cnf

echo "" >> /etc/mysql/my.cnf
echo "[mysqld]" >> /etc/mysql/my.cnf
echo "default_authentication_plugin=mysql_native_password" >> /etc/mysql/my.cnf
echo "skip-log-bin" >> /etc/mysql/my.cnf

sed -i "s/^max_connections.*=.*/max_connections={{ $mysqlMaxConnections() }}/" /etc/mysql/my.cnf

if grep -q "bind-address" /etc/mysql/mysql.conf.d/mysqld.cnf; then
  sed -i '/^bind-address/s/bind-address.*=.*/bind-address = */' /etc/mysql/mysql.conf.d/mysqld.cnf
else
  echo "bind-address = *" >> /etc/mysql/mysql.conf.d/mysqld.cnf
fi

mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER 'root'@'{{ $server->public_ipv4 }}' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER 'root'@'%' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO root@'{{ $server->public_ipv4 }}' WITH GRANT OPTION;"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO root@'%' WITH GRANT OPTION;"
service mysql restart

mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER '{{ config('eddy.server_defaults.database_name') }}'@'{{ $server->public_ipv4 }}' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER '{{ config('eddy.server_defaults.database_name') }}'@'%' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO '{{ config('eddy.server_defaults.database_name') }}'@'{{ $server->public_ipv4 }}' WITH GRANT OPTION;"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO '{{ config('eddy.server_defaults.database_name') }}'@'%' WITH GRANT OPTION;"
mysql --user="root" --password="{{ $server->database_password }}" -e "FLUSH PRIVILEGES;"

mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE DATABASE {{ config('eddy.server_defaults.database_name') }} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

service mysql restart

rm mysql-apt-config_0.8.24-1_all.deb