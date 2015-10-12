#!/bin/bash

# URL variables for downloading the fleet files from github
GIT_BASE_URL=https://raw.githubusercontent.com/icclab/cna-seed-project
GIT_BRANCH=${ZURMO_INIT_GIT_BRANCH:-"master"}
REL_SERVICEFILE_PATH=fleet

# decide if you want to just download the fleet files or also start the services
DOWNLOAD_FLEET_FILES=${ZURMO_INIT_DOWNLOAD_FLEET_FILES:-"True"}
START_SERVICES=${ZURMO_INIT_START_SERVICES:-"True"}

# Number of instances per service type
MYSQL_NUM_INSTANCES=${ZURMO_MYSQL_NUM_INSTANCES:-1}
MEMCACHE_NUM_INSTANCES=${ZURMO_MEMCACHE_NUM_INSTANCES:-2}
MYSQL_DEMODATA_NUM_INSTANCES=${ZURMO_MYSQL_DEMODATA_NUM_INSTANCES:-0}
APACHE_NUM_INSTANCES=${ZURMO_APACHE_NUM_INSTANCES:-2}
HAPROXY_NUM_INSTANCES=${ZURMO_HAPROXY_NUM_INSTANCES:-1}
DASHBOARD_NUM_INSTANCES=${ZURMO_DASHBOARD_NUM_INSTANCES:-1}
KIBANA_NUM_INSTANCES=${ZURMO_KIBANA_NUM_INSTANCES:-1}
ELASTICSEARCH_NUM_INSTANCES=${ZURMO_ELASTICSEARCH_NUM_INSTANCES:-1}
LOGSTASH_NUM_INSTANCES=${ZURMO_LOGSTASH_NUM_INSTANCES:-1}

# Names of the services
APACHE_NAME=apache
HAPROXY_NAME=haproxy
MEMCACHE_NAME=memcache
MYSQL_NAME=mysql
MYSQL_DEMODATA_NAME=mysql_demodata
DASHBOARD_NAME=dashboard
ELASTICSEARCH_NAME=elasticsearch
KIBANA_NAME=kibana
LOGSTASH_NAME=logstash
TSUNG_NAME=tsung
CONFIG_NAME=config
APPLICATION_NAME=application
LOG_COURIER_HAPROXY_NAME=log_courier_haproxy
LOG_COURIER_APACHE_NAME=log_courier_apache
LOG_COURIER_MYSQL_NAME=log_courier_mysql
LOG_COURIER_MEMCACHE_NAME=log_courier_memcache

TEMPLATE_NAMES=(${APACHE_NAME} ${ELASTICSEARCH_NAME} ${HAPROXY_NAME} ${KIBANA_NAME} ${LOGSTASH_NAME} ${MEMCACHE_NAME} ${MYSQL_NAME} ${TSUNG_NAME} ${DASHBOARD_NAME} ${LOG_COURIER_HAPROXY_NAME} ${LOG_COURIER_APACHE_NAME} ${LOG_COURIER_MYSQL_NAME} ${LOG_COURIER_MEMCACHE_NAME})
INSTANCE_NAMES=(${CONFIG_NAME} ${APPLICATION_NAME})

TEMPLATE_FILE_LOCATION=/home/core/templates
INSTANCE_FILE_LOCATION=/home/core/instances
TEMPLATE_PREFIX=zurmo_
INSTANCE_PREFIX=zurmo_
SERVICE_FILE_ENDING=.service
DISCOVERY_SERVICE_SUFFIX=_discovery

ENABLE_DISCOVERY_SERVICES=${ZURMO_INIT_ENABLE_DISCOVERY_SERVICES:-"True"}
URL_ENCODED_AT=%40
LOG_FILE_PATH=`pwd`/debug.log
EXEC_PATH=`pwd`

#create the directories for the template and instance files
mkdir -p ${TEMPLATE_FILE_LOCATION}
mkdir -p ${INSTANCE_FILE_LOCATION}

# comparing strings does ignore case
shopt -s nocasematch

# submits a file to fleet if the starting of services is enabled
function submit_fleet_file {
  FILE_PATH=$1
  if [ ${START_SERVICES} == "True" ]; then
    fleetctl submit ${FILE_PATH}
  fi
}

# logs and prints to stdout the string provided
# param: as many you want
function log_and_print {
	CONTENT=$@
	echo -e $CONTENT | tee -a ${LOG_FILE_PATH}
}

if [[ ${DOWNLOAD_FLEET_FILES} == "True" ]]; then

  # ----------------------
  # Download the fleet files from github
  # ----------------------

  # encodes the @ sign so it can be used in a url
  # param: URL
  function url_encode_at {
	  URL=$1
	  echo ${URL/@/${URL_ENCODED_AT}}
  }

  log_and_print "\n"
  log_and_print "################################################"
  log_and_print "## Download fleet files from github           ##"
  log_and_print "################################################"
  log_and_print "\n"

  log_and_print "Delete previously used files"

  if [ -d ${INSTANCE_FILE_LOCATION} ];
  then
	  rm ${INSTANCE_FILE_LOCATION}/*${SERVICE_FILE_ENDING}
	  rm ${TEMPLATE_FILE_LOCATION}/*${SERVICE_FILE_ENDING}
  fi

  log_and_print "Using git branch ${GIT_BRANCH}"

  log_and_print Get instance fleet files from github...
  for INSTANCE_NAME_PART in "${INSTANCE_NAMES[@]}"
  do
	  INSTANCE_NAME=${INSTANCE_PREFIX}${INSTANCE_NAME_PART}
    	  INSTANCE_FILE_NAME=${INSTANCE_NAME}${SERVICE_FILE_ENDING}

	  URL=${GIT_BASE_URL}/${GIT_BRANCH}/${INSTANCE_NAME}/${REL_SERVICEFILE_PATH}/${INSTANCE_FILE_NAME}
	  URL=$(url_encode_at $URL)
	  log_and_print Get file ${INSTANCE_FILE_NAME}
	  log_and_print File URL is ${URL}
	  curl ${URL} > ${INSTANCE_FILE_LOCATION}/${INSTANCE_FILE_NAME} 2>> ${LOG_FILE_PATH}
	  submit_fleet_file ${INSTANCE_FILE_LOCATION}/${INSTANCE_FILE_NAME}
  done

  log_and_print Get template fleet files from github...
  for TEMPLATE_NAME_PART in "${TEMPLATE_NAMES[@]}"
  do
          FLEET_PATH_PART=${REL_SERVICEFILE_PATH}

          if [[ ${TEMPLATE_NAME_PART} == log_courier* ]] ;
          then
                LOG_COURIER_INSTANCE=$(echo ${TEMPLATE_NAME_PART} | cut -f1,2 -d_ --complement)

                TEMPLATE_NAME=${INSTANCE_PREFIX}log_courier
                TEMPLATE_FILE_NAME=${TEMPLATE_PREFIX}${TEMPLATE_NAME_PART}@${SERVICE_FILE_ENDING}
                FLEET_PATH_PART=${LOG_COURIER_INSTANCE}/${REL_SERVICEFILE_PATH}
          else
	        TEMPLATE_NAME=${TEMPLATE_PREFIX}${TEMPLATE_NAME_PART}
	        TEMPLATE_FILE_NAME=${TEMPLATE_NAME}@${SERVICE_FILE_ENDING}
          fi

          URL=${GIT_BASE_URL}/${GIT_BRANCH}/${TEMPLATE_NAME}/${FLEET_PATH_PART}/${TEMPLATE_FILE_NAME}
	  URL=$(url_encode_at $URL)
	  log_and_print Get file ${TEMPLATE_FILE_NAME}
	  log_and_print File URL is ${URL}
	  curl ${URL} > ${TEMPLATE_FILE_LOCATION}/${TEMPLATE_FILE_NAME} 2>> ${LOG_FILE_PATH}

	  if [[ $ENABLE_DISCOVERY_SERVICES == "True" ]]; then
	    TEMPLATE_DISCOVERY_NAME=${TEMPLATE_PREFIX}${TEMPLATE_NAME_PART}${DISCOVERY_SERVICE_SUFFIX}
	    TEMPLATE_DISCOVERY_FILE_NAME=${TEMPLATE_DISCOVERY_NAME}@${SERVICE_FILE_ENDING}
	    URL=${GIT_BASE_URL}/${GIT_BRANCH}/${TEMPLATE_NAME}/${REL_SERVICEFILE_PATH}/${TEMPLATE_DISCOVERY_FILE_NAME}
	    URL=$(url_encode_at $URL)
	    log_and_print Get file ${TEMPLATE_DISCOVERY_FILE_NAME}
	    log_and_print File URL is ${URL}
	    curl ${URL} > ${TEMPLATE_FILE_LOCATION}/${TEMPLATE_DISCOVERY_FILE_NAME} 2>> ${LOG_FILE_PATH}
	  fi

  done

  # ----------------------
  # Create instance files from templates
  # ----------------------

  # creates a fleet instance file
  # param: (NR_INSTANCES, BASE_NR, TEMPLATE_NAME, HAS_DISCOVERY_SERVICE)
  function create_instance {
	  NR_INSTANCES=$1
	  BASE_NR=$2
	  TEMPLATE_NAME=$3
	  HAS_DISCOVERY_SERVICE=$4
	  
	  USE_DISCOVERY_SERVICE=$4
	  if [[ $ENABLE_DISCOVERY_SERVICES == "False" ]]; then
	    USE_DISCOVERY_SERVICE=0
	  fi

	  for (( i=0; i<${NR_INSTANCES}; i++ ))
	  do
		  INSTANCE_NR=$((BASE_NR + i))
		  log_and_print Create ${TEMPLATE_NAME} instance file: ${INSTANCE_NR}
		  INSTANCE_NAME=${TEMPLATE_NAME}@${INSTANCE_NR}${SERVICE_FILE_ENDING}

		  INSTANCE_FILE_NAME=${INSTANCE_FILE_LOCATION}/${INSTANCE_NAME}
		  rm -f ${INSTANCE_FILE_NAME}
		  ln -s ${TEMPLATE_FILE_LOCATION}/${TEMPLATE_NAME}@${SERVICE_FILE_ENDING} ${INSTANCE_FILE_NAME}
		  submit_fleet_file ${INSTANCE_FILE_NAME}		

		  if [ $USE_DISCOVERY_SERVICE = 1 ]; then
			  TEMPLATE_FILE_NAME=${TEMPLATE_FILE_LOCATION}/${TEMPLATE_NAME}${DISCOVERY_SERVICE_SUFFIX}@${SERVICE_FILE_ENDING}
			  DISCOVERY_INSTANCE_FILE_NAME=${INSTANCE_FILE_LOCATION}/${TEMPLATE_NAME}${DISCOVERY_SERVICE_SUFFIX}@${INSTANCE_NR}${SERVICE_FILE_ENDING}
			  echo create discovery file from template ${TEMPLATE_FILE_NAME}
			  rm -f ${DISCOVERY_INSTANCE_FILE_NAME}
			  ln -s ${TEMPLATE_FILE_NAME} ${DISCOVERY_INSTANCE_FILE_NAME}
			  submit_fleet_file ${INSTANCE_FILE_NAME}
		  fi
	  done
  }

  function create_apache {
	  create_instance $1 8080 ${TEMPLATE_PREFIX}${APACHE_NAME} 1
	  create_instance $1 8080 ${TEMPLATE_PREFIX}${LOG_COURIER_APACHE_NAME} 0
  }


  function create_haproxy {
	  create_instance $1 0 ${TEMPLATE_PREFIX}${HAPROXY_NAME} 1
	  create_instance $1 0 ${TEMPLATE_PREFIX}${LOG_COURIER_HAPROXY_NAME} 0
  }

  function create_memcache {
	  create_instance $1 11211 ${TEMPLATE_PREFIX}${MEMCACHE_NAME} 1
	  create_instance $1 11211 ${TEMPLATE_PREFIX}${LOG_COURIER_MEMCACHE_NAME} 0
  }


  function create_mysql {
	  create_instance $1 3306 ${TEMPLATE_PREFIX}${MYSQL_NAME} 1
	  create_instance $1 3306 ${TEMPLATE_PREFIX}${LOG_COURIER_MYSQL_NAME} 0        
  }


  function create_mysql_demodata {
	  create_instance $1 3306 ${TEMPLATE_PREFIX}${MYSQL_DEMODATA_NAME} 1
	  create_instance $1 3306 ${TEMPLATE_PREFIX}${LOG_COURIER_MYSQL_NAME} 0      
  }
  
  function create_dashboard {
	  create_instance $1 0 ${TEMPLATE_PREFIX}${DASHBOARD_NAME} 0
  }

  function create_kibana {
	  create_instance $1 8010 ${TEMPLATE_PREFIX}${KIBANA_NAME} 0
  }

  function create_elasticsearch {
	  create_instance $1 0 ${TEMPLATE_PREFIX}${ELASTICSEARCH_NAME} 1
  }

  function create_logstash {
	  create_instance $1 5000 ${TEMPLATE_PREFIX}${LOGSTASH_NAME} 1
  }

  log_and_print "\n"
  log_and_print "################################################"
  log_and_print "## Create fleet instance files from templates ##"
  log_and_print "################################################"
  log_and_print "\n"

  create_mysql $MYSQL_NUM_INSTANCES
  create_memcache $MEMCACHE_NUM_INSTANCES
  create_mysql_demodata $MYSQL_DEMODATA_NUM_INSTANCES
  create_apache $APACHE_NUM_INSTANCES
  create_haproxy $HAPROXY_NUM_INSTANCES
  create_dashboard $DASHBOARD_NUM_INSTANCES 
  create_kibana $KIBANA_NUM_INSTANCES
  create_elasticsearch $ELASTICSEARCH_NUM_INSTANCES
  create_logstash $LOGSTASH_NUM_INSTANCES
fi

#  Check if etcd cluster is up and we're leader
lead=`curl -L -s http://localhost:4001/v2/stats/leader | awk -F, 'match($0, /leader\":\"([0-9a-zA-Z]+)/, lead) {print lead[1]}'`
myself=`curl -L -s http://localhost:4001/v2/stats/self | awk -F, 'match($0, /id\":\"([0-9a-zA-Z]+)/, lead) {print lead[1]}'`
log_and_print "Lead: $lead"
log_and_print "Myself: $myself"
output_l=`curl -L -s http://localhost:4001/v2/stats/leader`
output_s=`curl -L -s http://localhost:4001/v2/stats/self`
log_and_print "$output_l"
log_and_print "$output_s"


if [[ ${START_SERVICES} == "True" ]]; then
  # ----------------------
  # Start the services
  # ----------------------

  log_and_print "\n"
  log_and_print Load fleet services
  log_and_print "\n"

  fleetctl load ${INSTANCE_FILE_LOCATION}/*${SERVICE_FILE_ENDING} >> ${LOG_FILE_PATH}
  printf "Loaded unit files:\n"
  fleetctl list-unit-files
  printf "\n"

  sleep 5

  cd ${INSTANCE_FILE_LOCATION}

  log_and_print Starting instances
  fleetctl start *${SERVICE_FILE_ENDING}
  
  
  cd $EXEC_PATH
  log_and_print Running instances:
  fleetctl list-units | tee -a ${LOG_FILE_PATH}
fi
