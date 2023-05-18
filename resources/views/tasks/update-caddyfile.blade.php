<x-task-shell-defaults />

@php $caddyfilePath = $site->files()->caddyfile()->path; $tmpSuffix = '.'.Str::random(); @endphp

# Create a temporary file with the new Caddyfile
cat > {!! $caddyfilePath.$tmpSuffix !!} << EOF
{!! $caddyfile !!}

EOF

# Validate the Caddyfile
set +e
caddy validate --config {!! $caddyfilePath.$tmpSuffix !!} --adapter caddyfile

# If the Caddyfile is invalid, remove the temporary file and exit
if [ $? -ne 0 ]; then
    rm {!! $caddyfilePath.$tmpSuffix !!}
    exit 1
fi

set -e

# Format the Caddyfile
caddy fmt {!! $caddyfilePath.$tmpSuffix !!} --overwrite

# Replace the old Caddyfile with the new one
mv {!! $caddyfilePath.$tmpSuffix !!} {!! $caddyfilePath !!}

# Reload Caddy
sudo /usr/sbin/service caddy reload

