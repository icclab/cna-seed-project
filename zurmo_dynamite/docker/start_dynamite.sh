#!/bin/bash

DYNAMITE_CONFIG_PATH=/etc/dynamite/dynamite.yaml
SERVICE_FOLDER_PATH=/etc/dynamite/fleet-files
RABBITMQ_OPTION=""
DYNAMITE_DOWNLOAD_FILES=${DYNAMITE_DOWNLOAD_FILES:-"True"}

if [ ! -z ${RABBITMQ_ENDPOINT} ]; then
	RABBITMQ_OPTION="--rabbitmq_endpoint ${RABBITMQ_ENDPOINT}"
fi

if [ ${DYNAMITE_DOWNLOAD_FILES} == "True" ]; then
	/download_files.sh
fi
sed -i "s/<FLEET_IP>/${FLEET_IP}/g" ${DYNAMITE_CONFIG_PATH}
sed -i "s/<FLEET_PORT>/${FLEET_PORT}/g" ${DYNAMITE_CONFIG_PATH}
exec /usr/local/bin/dynamite --config_file ${DYNAMITE_CONFIG_PATH} --service_folder ${SERVICE_FOLDER_PATH} --etcd_endpoint ${ETCD_ENDPOINT} --fleet_endpoint ${FLEET_IP}:${FLEET_PORT} ${RABBITMQ_OPTION} 
