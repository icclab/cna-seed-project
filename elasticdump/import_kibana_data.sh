#!/bin/bash

# This script imports dumped kibana data into elasticsearch
# Execute with parameters:
# 1: Path to data dump
# 2: Elasticsearch endpoint (HTTP) with port

DUMP_FILE_PATH=${1:-"/tmp/dump.json"}
ELASTICSEARCH_ENDPOINT=${2:-"172.17.8.101:9200"}

if [ $# -lt 2 ]; then
	echo "Usage: $0 /path/to/dump/file.json elastic.search.instance:9200";
	exit;
fi

docker run --rm -t -e "ELASTICSEARCH=${ELASTICSEARCH_ENDPOINT}" -v ${DUMP_FILE_PATH}:/dump.json icclabcna/elasticdump:logging-dev /import.sh
