#!/bin/bash

DYNAMITE_CONFIG_PATH=/etc/dynamite/dynamite.yaml
SERVICE_FOLDER_PATH=/etc/dynamite/fleet-files
RABBITMQ_ENDPOINT=127.0.0.1:5672

if [ -f ${DYNAMITE_CONFIG_PATH} ]; then
	sed -i "s/<FLEET_IP>/${FLEET_IP}/g" ${DYNAMITE_CONFIG_PATH}
	sed -i "s/<FLEET_PORT>/${FLEET_PORT}/g" ${DYNAMITE_CONFIG_PATH}
	/usr/local/bin/dynamite --config_file ${DYNAMITE_CONFIG_PATH} --service_folder ${SERVICE_FOLDER_PATH} --etcd_endpoint ${ETCD_ENDPOINT} --rabbitmq_endpoint ${RABBITMQ_ENDPOINT} 
else
	/usr/local/bin/dynamite --etcd_endpoint ${ETCD_ENDPOINT} --rabbitmq_endpoint ${RABBITMQ_ENDPOINT}
fi
