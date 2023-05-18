cd {!! $site->path !!}

echo "Downloading the latest Wordpress release..."
curl -LOs https://wordpress.org/latest.zip

echo "Extracting Wordpress repository..."
unzip -qq latest.zip
mv wordpress/* {!! $repositoryDirectory !!}/
rm latest.zip
rm -rf wordpress

CONFIG_PATH="{!! $repositoryDirectory !!}/wp-config.php"
SAMPLE_CONFIG_PATH="{!! $repositoryDirectory !!}/wp-config-sample.php"

if [ ! -f $CONFIG_PATH ] && [ -f $SAMPLE_CONFIG_PATH ]; then
    cp $SAMPLE_CONFIG_PATH $CONFIG_PATH
    cd {!! $repositoryDirectory !!}

    @foreach($env as $search => $replace)
        sed -i --follow-symlinks "s|^define( '{{ $search }}',.*|define('{{ $search }}', '{!! $replace !!}');|g" wp-config.php
    @endforeach

    sed -i --follow-symlinks '/\/* Add any custom values between this line and the "stop editing" line.*/a define( "DISABLE_WP_CRON", true );' wp-config.php

fi

echo "Done!"
