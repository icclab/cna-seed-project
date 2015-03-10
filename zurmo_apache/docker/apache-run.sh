#!/bin/bash
. /etc/apache2/envvars

set -eo pipefail

export ETCD_PORT=${ETCD_PORT:-4001}
export HOST_IP=${HOST_IP:-172.17.42.1}
export ETCD=$HOST_IP:$ETCD_PORT

echo "[apache] booting container. ETCD: $ETCD."

# Try to make initial configuration every 5 seconds until successful
until confd -onetime -node $ETCD -config-file /etc/confd/conf.d/zurmo_perInstance.toml; do
    echo "[apache] waiting for confd to create initial apache configuration."
    sleep 5
done

# Put a continual polling `confd` process into the background to watch
# for changes every 10 seconds
confd -interval 10 -node $ETCD -config-file /etc/confd/conf.d/zurmo_perInstance.toml &
echo "[apache] confd is now monitoring etcd for changes..."

# set file permissions of www-directory
/set_data_permissions.sh

exec dockerize \
     -stdout /var/log/apache2/access.log \
     -stderr /var/log/apache2/error.log \
     /usr/sbin/apache2 -DFOREGROUND