echo "Configure swap"

if [ ! -f /swapfile ]; then
    fallocate -l {{ $swapInMegabytes() }}M /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile || true
    echo "/swapfile none swap sw 0 0" >> /etc/fstab
    echo "vm.swappiness={{ $swappiness() }}" >> /etc/sysctl.conf
    echo "vm.vfs_cache_pressure=50" >> /etc/sysctl.conf
fi