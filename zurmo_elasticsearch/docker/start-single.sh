#!/bin/bash

set -eo pipefail

echo "[elasticsearch] booting container"
echo "[elasticsearch] setting publish ip ${HOST_PRIVATE_IPV4}"

sed -i "s/<HOST_IP>/${HOST_PRIVATE_IPV4}/g" /data/elasticsearch.yml
echo "[elasticsearch] elasticsearch configuration is now:"
cat /data/elasticsearch.yml

elasticsearch -Des.config=/data/elasticsearch.yml

