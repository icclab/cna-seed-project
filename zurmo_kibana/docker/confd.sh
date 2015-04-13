#!/bin/bash

set -eo pipefail

export ETCD_ENDPOINT=${ETCD_ENDPOINT:-172.17.42.1:4001}
TOML_PATH=/etc/confd/conf.d/kibana.toml

echo "[kibana] booting container"
echo "[kibana] using etcd endpoint ${ETCD_ENDPOINT}"

# Try to make initial configuration every 5 seconds until successful
until confd -onetime -node $ETCD_ENDPOINT -config-file ${TOML_PATH}; do
    echo "[kibana] waiting for confd to create initial configuration."
    sleep 5
done

# Put a continual polling `confd` process into the background to watch
# for changes every 10 seconds
confd -interval 10 -node $ETCD_ENDPOINT -config-file ${TOML_PATH}


