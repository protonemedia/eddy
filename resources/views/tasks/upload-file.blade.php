<x-task-shell-defaults />

mkdir -p {!! $directory() !!}

cat > {!! $path !!} << 'EOF'
{!! trim($contents) !!}
EOF