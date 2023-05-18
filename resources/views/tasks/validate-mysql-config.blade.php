<x-task-shell-defaults />

cat > {!! $path !!} << 'EOF'
{!! $mysqlConfig !!}
EOF

# Validate the MySql Config
set +e
mysqld --defaults-file={!! $path !!} --validate-config

# If the MySql Config is invalid, remove the temporary file and exit
if [ $? -ne 0 ]; then
    rm {!! $path !!}
    exit 1
fi

set -e

rm {!! $path !!}

exit $EXIT_CODE