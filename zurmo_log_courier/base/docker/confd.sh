#!/bin/bash

set -eo pipefail

export ETCD_ENDPOINT=${ETCD_ENDPOINT:-172.17.42.1:4001}

TOML_PATH=/etc/confd/conf.d/log-courier.toml
CONTAINER_NAME="log-courier"

echo "[${CONTAINER_NAME}] booting container"

mkdir -p /var/log/log-courier && touch /var/log/log-courier/log-courier.log

sed -i s/%hostname%/${LOG_SRC_HOSTNAME}/g /etc/confd/templates/*.tmpl
sed -i s/%service-id%/${LOG_SRC_ID}/g /etc/confd/templates/*.tmpl

# Try to make initial configuration every 5 seconds until successful
until confd -onetime -node $ETCD_ENDPOINT -config-file ${TOML_PATH}; do
    echo "[${CONTAINER_NAME}] waiting for confd to create initial ${CONTAINER_NAME} configuration."
    sleep 5
done

# Put a continual polling `confd` process into the background to watch
# for changes every 10 seconds
echo "[${CONTAINER_NAME}] confd is now monitoring etcd for changes..."
confd -interval 10 -node $ETCD_ENDPOINT -config-file ${TOML_PATH}
