echo "Download and install Composer dependency manager"

curl -sS https://getcomposer.org/installer | php -- --2
mv composer.phar /usr/local/bin/composer

echo "{!! $server->username !!} ALL=(root) NOPASSWD: /usr/local/bin/composer self-update*" > /etc/sudoers.d/composer

# Create default auth.json

mkdir -p /home/{!! $server->username !!}/.config/composer
touch /home/{!! $server->username !!}/.config/composer/auth.json

cat > /home/{!! $server->username !!}/.config/composer/auth.json << 'EOF'
{
  "bearer": {},
  "bitbucket-oauth": {},
  "github-oauth": {},
  "gitlab-oauth": {},
  "gitlab-token": {},
  "http-basic": {}
}
EOF

chown -R {!! $server->username !!}:{!! $server->username !!} /home/{!! $server->username !!}/.config/composer
chmod 600 /home/{!! $server->username !!}/.config/composer/auth.json
