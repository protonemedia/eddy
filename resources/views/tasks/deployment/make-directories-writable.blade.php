@foreach($writeableDirectories() as $directory)
    DIRECTORY_IS_WRITEABLE=$(getfacl -p {!! $releaseDirectory !!}/{!! $directory !!} | grep "^user:{!! $site->user !!}:.*w" | wc -l)

    if [ $DIRECTORY_IS_WRITEABLE -eq 0 ]; then
        # Make the directory writable (without sudo)
        setfacl -L -m u:{!! $site->user !!}:rwX {!! $releaseDirectory !!}/{!! $directory !!}
        setfacl -dL -m u:{!! $site->user !!}:rwX {!! $releaseDirectory !!}/{!! $directory !!}
    fi

@endforeach
