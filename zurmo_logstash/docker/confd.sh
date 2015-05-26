#!/bin/bash

set -eo pipefail

ETCD_PORT=${ETCD_PORT:-4001}
ETCD_IP=${ETCD_IP:-"172.17.42.1"}
export ETCD_ENDPOINT=${ETCD_IP}:${ETCD_PORT}
TOML_PATH=/etc/confd/conf.d/elasticsearch.toml

echo "[logstash] booting container"
echo "[logstash] using etcd endpoint ${ETCD_ENDPOINT}"

echo "[logstash] set publish ip ${HOST_PRIVATE_IPV4}"
sed -i "s/<HOST_IP>/${HOST_PRIVATE_IPV4}/g" /etc/confd/templates/elasticsearch.yml.tmpl
sed -i "s/<NODE_NAME>/logstash_${HOST_PRIVATE_IPV4}/g" /etc/confd/templates/elasticsearch.yml.tmpl 
sed -i "s/<ETCD_IP>/${ETCD_IP}/g" /logstash/logstash_courier.cfg
sed -i "s/<ETCD_PORT>/${ETCD_PORT}/g" /logstash/logstash_courier.cfg

echo "[logstash] elasticsearch cluster configuration template is now:"
cat /etc/confd/templates/elasticsearch.yml.tmpl

# Try to make initial configuration every 5 seconds until successful
until confd -onetime -node $ETCD_ENDPOINT -config-file ${TOML_PATH}; do
    echo "[logstash] waiting for confd to create initial configuration."
    sleep 5
done

echo "[logstash] elasticsearch cluster configuration is now:"
cat /logstash/elasticsearch.yml

# Put a continual polling `confd` process into the background to watch
# for changes every 10 seconds
confd -interval 10 -node $ETCD_ENDPOINT -config-file ${TOML_PATH}


