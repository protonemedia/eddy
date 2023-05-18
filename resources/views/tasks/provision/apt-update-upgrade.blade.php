@include('tasks.apt-functions')

echo "Update package repositories and upgrade packages"

#  This ensures that the cloud-init bundle is not overwritten by a different version when running apt-get upgrade.
waitForAptUnlock
apt-mark hold cloud-init

# Update package repositories
waitForAptUnlock
apt-get update -y

# Install software-properties-common
waitForAptUnlock
apt-get install software-properties-common -y

# Add universe repository
waitForAptUnlock
add-apt-repository universe -y

# Update package repositories
waitForAptUnlock
apt-get update -y

# Upgrade packages
waitForAptUnlock
apt-get upgrade -y
