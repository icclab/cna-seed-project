#!/bin/bash

set -eo pipefail

export ETCD_PORT=${ETCD_PORT:-4001}
export HOST_IP=${HOST_IP:-10.1.42.1}
export ETCD=$HOST_IP:$ETCD_PORT

echo "[kibana] booting container. ETCD: $ETCD."

# Try to make initial configuration every 5 seconds until successful
until confd -onetime -node $ETCD -config-file /etc/confd/conf.d/kibana.toml; do
    echo "[kibana] waiting for confd to create initial kibana configuration."
    sleep 5
done

# Put a continual polling `confd` process into the background to watch
# for changes every 10 seconds
confd -interval 10 -node $ETCD -config-file /etc/confd/conf.d/kibana.toml &
echo "[kibana] confd is now monitoring etcd for changes..."

nginx
tail -f /var/log/nginx/*.log
