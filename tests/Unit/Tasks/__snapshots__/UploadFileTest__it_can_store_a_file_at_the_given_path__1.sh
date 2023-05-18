set -eu
export DEBIAN_FRONTEND=noninteractive

mkdir -p /home/protone

cat > /home/protone/test.txt << 'EOF'
new content
EOF