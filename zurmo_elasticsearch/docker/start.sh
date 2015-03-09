#!/bin/bash

set -eo pipefail

export ETCD_ENDPOINT=${ETCD_ENDPOINT:-10.1.42.1:4001}

echo "[elasticsearch] booting container"

# Try to make initial configuration every 5 seconds until successful
until confd -onetime -node $ETCD_ENDPOINT -config-file /etc/confd/conf.d/elasticsearch.toml; do
    echo "[elasticsearch] waiting for confd to create initial elasticsearch configuration."
    sleep 5
done

# Put a continual polling `confd` process into the background to watch
# for changes every 10 seconds
confd -interval 10 -node $ETCD_ENDPOINT -config-file /etc/confd/conf.d/elasticsearch.toml &
echo "[elasticsearch] confd is now monitoring etcd for changes..."

# Start the HAProxy service using the generated config
echo "[elasticsearch] starting elasticsearch service..."
/elasticsearch/bin/elasticsearch -Des.config=/data/elasticsearch.yml

tail -f /data/log/*.log