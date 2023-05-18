echo "Enhance SSH security"

sed -i "/PasswordAuthentication yes/d" /etc/ssh/sshd_config
echo "PasswordAuthentication no" | tee -a /etc/ssh/sshd_config
service ssh restart

echo "Setup SSH keys for root"

if [ ! -d /root/.ssh ]
then
  mkdir -p /root/.ssh
  touch /root/.ssh/authorized_keys
fi
