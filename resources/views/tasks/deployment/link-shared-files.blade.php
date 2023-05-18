@foreach($sharedFiles() as $file)
    # Create directories in shared and release directories if they don't exist
    mkdir -p {!! $releaseDirectory !!}/{!! dirname($file) !!}
    mkdir -p {!! $sharedDirectory !!}/{!! dirname($file) !!}

    # If the shared file does not exist, but the release file does, copy the release file to shared
    if [ ! -f "{!! $sharedDirectory !!}/{!! $file !!}" ] && [ -f "{!! $releaseDirectory !!}/{!! $file !!}" ]; then
        cp {!! $releaseDirectory !!}/{!! $file !!} {!! $sharedDirectory !!}/{!! $file !!}
    fi

    # If the shared file still does not exist, create it
    if [ ! -f "{!! $sharedDirectory !!}/{!! $file !!}" ]; then
        touch {!! $sharedDirectory !!}/{!! $file !!}
    fi

    # If the release file exists, remove it
    if [ -f "{!! $releaseDirectory !!}/{!! $file !!}" ]; then
        rm -rf {!! $releaseDirectory !!}/{!! $file !!}
    fi

    # Create symlink
    ln -nfs --relative {!! $sharedDirectory !!}/{!! $file !!} {!! $releaseDirectory !!}/{!! $file !!}

@endforeach
