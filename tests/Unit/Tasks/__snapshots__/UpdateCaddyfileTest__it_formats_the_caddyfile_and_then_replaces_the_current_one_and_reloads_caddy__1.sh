set -eu
export DEBIAN_FRONTEND=noninteractive

# Create a temporary file with the new Caddyfile
cat > /home/eddy/protone.dev/Caddyfile.0IJ7PAPA18XR05Jt << EOF
new content

EOF

# Validate the Caddyfile
set +e
caddy validate --config /home/eddy/protone.dev/Caddyfile.0IJ7PAPA18XR05Jt --adapter caddyfile

# If the Caddyfile is invalid, remove the temporary file and exit
if [ $? -ne 0 ]; then
    rm /home/eddy/protone.dev/Caddyfile.0IJ7PAPA18XR05Jt
    exit 1
fi

set -e

# Format the Caddyfile
caddy fmt /home/eddy/protone.dev/Caddyfile.0IJ7PAPA18XR05Jt --overwrite

# Replace the old Caddyfile with the new one
mv /home/eddy/protone.dev/Caddyfile.0IJ7PAPA18XR05Jt /home/eddy/protone.dev/Caddyfile

# Reload Caddy
sudo /usr/sbin/service caddy reload

