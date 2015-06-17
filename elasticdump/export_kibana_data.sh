#!/bin/bash

# This script exports kibana data (visualization/dashboard) to a specified file
# Run this script with the parameters
# 1: Path to dump file, defaults to /tmp/dump.json
# 2: Elasticsearch instance (HTTP) with port

DUMP_FILE_PATH=${1:-"/tmp/dump.json"}
ELASTICSEARCH_INSTANCE=${2:-"172.17.8.101:9200"}

if [ $# -lt 2 ]; then
	echo "Usage $0 /path/to/dump/file elastic.search.instance:9200";
	exit;
fi

docker run --rm -it -e "ELASTICSEARCH=${ELASTICSEARCH_INSTANCE}" icclabcna/elasticdump /export.sh > ${DUMP_FILE_PATH}

