set -eu
export DEBIAN_FRONTEND=noninteractive

cat > /tmp/mysql-0IJ7PAPA18XR05Jt.cnf << 'EOF'
[mysqld]
EOF

# Validate the MySql Config
set +e
mysqld --defaults-file=/tmp/mysql-0IJ7PAPA18XR05Jt.cnf --validate-config

# If the MySql Config is invalid, remove the temporary file and exit
if [ $? -ne 0 ]; then
    rm /tmp/mysql-0IJ7PAPA18XR05Jt.cnf
    exit 1
fi

set -e

rm /tmp/mysql-0IJ7PAPA18XR05Jt.cnf

exit $EXIT_CODE