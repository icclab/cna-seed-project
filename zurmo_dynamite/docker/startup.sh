#!/bin/bash

if [ ! -z ${RABBITMQ_ENDPOINT} ]; then
	supervisorctl start rabbitmq
	
	while [ -z "`netstat -tln | grep 5672`" ]; do
		echo "Waiting for rabbitmq to start ..."
		sleep 1
	done
	echo "rabbitmq started"
fi

echo "start dynamite"
supervisorctl start dynamite
