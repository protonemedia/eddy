echo "Rename existing user 1000 if it exists, otherwise create a new user"

if getent passwd 1000 > /dev/null 2>&1; then
    echo "Renaming existing user 1000"
    OLD_USERNAME=$(getent passwd 1000 | cut -d: -f1)
    (pkill -9 -u $OLD_USERNAME || true)
    (pkill -KILL -u $OLD_USERNAME || true)
    usermod --login {{ $server->username }} --move-home --home /home/{{ $server->username }} $OLD_USERNAME
    groupmod --new-name {{ $server->username }} $OLD_USERNAME
else
    echo "Setup default user"
    useradd {{ $server->username }}
fi

echo "Create the user's home directory"

mkdir -p /home/{{ $server->username }}/{{ $server->working_directory }}
mkdir -p /home/{{ $server->username }}/.ssh

echo "Add user to groups"

adduser {{ $server->username }} sudo
id {{ $server->username }}
groups {{ $server->username }}

echo "Set shell"

chsh -s /bin/bash {{ $server->username }}

echo "Init default profile/bashrc"

cp /root/.bashrc /home/{{ $server->username }}/.bashrc
cp /root/.profile /home/{{ $server->username }}/.profile

echo "Copy SSH settings from root and create new key"

cp /root/.ssh/authorized_keys /home/{{ $server->username }}/.ssh/authorized_keys
cp /root/.ssh/known_hosts /home/{{ $server->username }}/.ssh/known_hosts
ssh-keygen -f /home/{{ $server->username }}/.ssh/id_rsa -t rsa -N ''

@if($sshKeys->isNotEmpty())
echo "Add SSH keys to authorized_keys"

@foreach($sshKeys as $sshKey)
cat <<EOF >> /home/{{ $server->username }}/.ssh/authorized_keys
{{ $sshKey->public_key }}
EOF

@endforeach
@endif

echo "Set password"

PASSWORD=$(mkpasswd -m sha-512 {{ $server->password }})
usermod --password $PASSWORD {{ $server->username }}

echo "Add default Caddy page"

mkdir -p /home/{{ $server->username }}/default
cat <<EOF >> /home/{{ $server->username }}/default/index.html
This server is managed by <a href="{{ config('app.url') }}">{{ config('app.name') }}</a>.

EOF

echo "Fix user permissions"

chown -R {{ $server->username }}:{{ $server->username }} /home/{{ $server->username }}
chmod -R 755 /home/{{ $server->username }}
chmod 700 /home/{{ $server->username }}/.ssh
chmod 700 /home/{{ $server->username }}/.ssh/id_rsa
chmod 600 /home/{{ $server->username }}/.ssh/authorized_keys