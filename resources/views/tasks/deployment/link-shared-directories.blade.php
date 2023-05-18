@foreach($sharedDirectories() as $directory)
    if [ ! -d "{!! $sharedDirectory !!}/{!! $directory !!}" ]; then
        # Create shared directory if it does not exist.
        mkdir -p {!! $sharedDirectory !!}/{!! $directory !!}

        if [ -d "{!! $releaseDirectory !!}/{!! $directory !!}" ]; then
            # Copy contents of release directory to shared directory if it exists.
            cp -r {!! $releaseDirectory !!}/{!! $directory !!} {!! $sharedDirectory !!}/{!! dirname($directory) !!}
        fi
    fi

    #  Remove shared directory from release directory if it exists.
    rm -rf {!! $releaseDirectory !!}/{!! $directory !!}

    # Create parent directory of shared directory in release directory if it does not exist,
    # otherwise symlink will fail.
    mkdir -p `dirname {!! $releaseDirectory !!}/{!! $directory !!}`

    # Symlink shared directory to release directory.
    ln -nfs --relative {!! $sharedDirectory !!}/{!! $directory !!} {!! $releaseDirectory !!}/{!! $directory !!}

@endforeach
