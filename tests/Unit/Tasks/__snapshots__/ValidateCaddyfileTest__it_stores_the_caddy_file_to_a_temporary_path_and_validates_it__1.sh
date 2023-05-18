set -eu
export DEBIAN_FRONTEND=noninteractive

cat > /tmp/caddyfile-0IJ7PAPA18XR05Jt.caddyfile << 'EOF'
server { }
EOF

# Validate the Caddyfile
set +e
caddy validate --config /tmp/caddyfile-0IJ7PAPA18XR05Jt.caddyfile --adapter caddyfile

# If the Caddyfile is invalid, remove the temporary file and exit
if [ $? -ne 0 ]; then
    rm /tmp/caddyfile-0IJ7PAPA18XR05Jt.caddyfile
    exit 1
fi

set -e

rm /tmp/caddyfile-0IJ7PAPA18XR05Jt.caddyfile

exit $EXIT_CODE