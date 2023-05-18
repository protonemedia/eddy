cd {!! $site->path !!}

ENV_PATH="{!! $site->zero_downtime_deployment ? $sharedDirectory : $repositoryDirectory !!}/.env"
EXAMPLE_ENV_PATH="{!! $site->zero_downtime_deployment ? $releaseDirectory : $repositoryDirectory !!}/.env.example"

if [ ! -f $ENV_PATH ] && [ -f $EXAMPLE_ENV_PATH ]; then
    cp $EXAMPLE_ENV_PATH $ENV_PATH
    cd {!! $site->zero_downtime_deployment ? $sharedDirectory : $repositoryDirectory !!}

    @foreach($env as $search => $replace)
        sed -i --follow-symlinks "s|^{{ $search }}=.*|{{ $search }}={{ $replace }}|g" .env
    @endforeach

fi
