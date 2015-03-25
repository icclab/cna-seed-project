#!/bin/bash

set -eo pipefail

export ETCD_ENDPOINT=${ETCD_ENDPOINT:-172.17.42.1:4001}

TOML_PATH=/logstash_forwarder/logstash_forwarder.toml
CONTAINER_NAME="logstash-forwarder"

echo "[${CONTAINER_NAME}] booting container"

# Try to make initial configuration every 5 seconds until successful
until confd -onetime -node $ETCD_ENDPOINT -config-file ${TOML_PATH}; do
    echo "[${CONTAINER_NAME}] waiting for confd to create initial ${CONTAINER_NAME} configuration."
    sleep 5
done

# Put a continual polling `confd` process into the background to watch
# for changes every 10 seconds
confd -interval 10 -node $ETCD_ENDPOINT -config-file ${TOML_PATH} &
echo "[${CONTAINER_NAME}] confd is now monitoring etcd for changes..."

# service logstash-forwarder start

tail -f /var/log/logstash-forwarder/*


