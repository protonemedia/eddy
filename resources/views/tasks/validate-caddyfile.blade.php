<x-task-shell-defaults />

cat > {!! $path !!} << 'EOF'
{!! $caddyfile !!}
EOF

# Validate the Caddyfile
set +e
caddy validate --config {!! $path !!} --adapter caddyfile

# If the Caddyfile is invalid, remove the temporary file and exit
if [ $? -ne 0 ]; then
    rm {!! $path !!}
    exit 1
fi

set -e

rm {!! $path !!}

exit $EXIT_CODE