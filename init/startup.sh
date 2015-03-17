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

# Names of the services
APACHE_NAME=zurmo_apache
HAPROXY_NAME=zurmo_haproxy
MEMCACHE_NAME=zurmo_memcache
MYSQL_NAME=zurmo_mysql
MYSQL_DEMODATA_NAME=zurmo_mysql_demodata

TEMPLATE_FILE_LOCATION=~/templates
INSTANCE_FILE_LOCATION=~/instances
TEMPLATE_PREFIX=zurmo_
INSTANCE_PREFIX=zurmo_
TEMPLATE_NAMES=(apache elasticsearch haproxy kibana logstash memcache mysql tsung)
INSTANCE_NAMES=(config application)
SERVICE_FILE_ENDING=.service
DISCOVERY_SERVICE_SUFFIX=_discovery
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

  # logs and prints to stdout the string provided
  # param: as many you want
  function log_and_print {
	  CONTENT=$@
	  echo -e $CONTENT | tee -a ${LOG_FILE_PATH}
  }

  log_and_print "\n"
  log_and_print "################################################"
  log_and_print "## Download fleet files from github           ##"
  log_and_print "################################################"
  log_and_print "\n"

  log_and_print "Delete previously used files"
  rm ${INSTANCE_FILE_LOCATION}/*${SERVICE_FILE_ENDING}
  rm ${TEMPLATE_FILE_LOCATION}/*${SERVICE_FILE_ENDING}

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
	  submit_fleet_file ${INSTANCE_FILE_NAME}
  done

  log_and_print Get template fleet files from github...
  for TEMPLATE_NAME_PART in "${TEMPLATE_NAMES[@]}"
  do
	  TEMPLATE_NAME=${TEMPLATE_PREFIX}${TEMPLATE_NAME_PART}
	  TEMPLATE_FILE_NAME=${TEMPLATE_NAME}@${SERVICE_FILE_ENDING}
	  URL=${GIT_BASE_URL}/${GIT_BRANCH}/${TEMPLATE_NAME}/${REL_SERVICEFILE_PATH}/${TEMPLATE_FILE_NAME}
	  URL=$(url_encode_at $URL)
	  log_and_print Get file ${TEMPLATE_FILE_NAME}
	  log_and_print File URL is ${URL}
	  curl ${URL} > ${TEMPLATE_FILE_LOCATION}/${TEMPLATE_FILE_NAME} 2>> ${LOG_FILE_PATH}

	  TEMPLATE_DISCOVERY_NAME=${TEMPLATE_PREFIX}${TEMPLATE_NAME_PART}${DISCOVERY_SERVICE_SUFFIX}
	  TEMPLATE_DISCOVERY_FILE_NAME=${TEMPLATE_DISCOVERY_NAME}@${SERVICE_FILE_ENDING}
	  URL=${GIT_BASE_URL}/${GIT_BRANCH}/${TEMPLATE_NAME}/${REL_SERVICEFILE_PATH}/${TEMPLATE_DISCOVERY_FILE_NAME}
	  URL=$(url_encode_at $URL)
	  log_and_print Get file ${TEMPLATE_DISCOVERY_FILE_NAME}
	  log_and_print File URL is ${URL}
	  curl ${URL} > ${TEMPLATE_FILE_LOCATION}/${TEMPLATE_DISCOVERY_FILE_NAME} 2>> ${LOG_FILE_PATH}

  done

  # ----------------------
  # Create instance files from templates
  # ----------------------

  # transforms all lowercase characters in uppercase characters
  # param: string
  function uppercase {
	  echo $1 | tr '[:lower:]' '[:upper:]'
  }

  APACHE_NAME=zurmo_apache
  HAPROXY_NAME=zurmo_haproxy
  MEMCACHE_NAME=zurmo_memcache
  MYSQL_NAME=zurmo_mysql
  MYSQL_DEMODATA_NAME=zurmo_mysql_demodata

  declare -A INSTANCES
  INSTANCES[${APACHE_NAME}]=""
  INSTANCES[${HAPROXY_NAME}]=""
  INSTANCES[${MEMCACHE_NAME}]=""
  INSTANCES[${MYSQL_NAME}]=""
  INSTANCES[${MYSQL_DEMODATA_NAME}]=""

  # adds instance file names to the dictionary
  # param: template name, instance name
  function add_instance {
	  TEMPLATE_NAME=$1
	  INSTANCE_NAME=$2
	  
	  INSTANCES[${TEMPLATE_NAME}]+=" ${INSTANCE_NAME}"
  }

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
		  add_instance $TEMPLATE_NAME $INSTANCE_NAME		

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
	  create_instance $1 8080 ${APACHE_NAME} 1
  }


  function create_haproxy {
	  create_instance $1 0 ${HAPROXY_NAME} 1
  }

  function create_memcache {
	  create_instance $1 11211 ${MEMCACHE_NAME} 1
  }


  function create_mysql {
	  create_instance $1 3306 ${MYSQL_NAME} 1        
  }


  function create_mysql_demodata {
	  create_instance $1 3306 ${MYSQL_DEMODATA_NAME} 1        
  }

  function create_haproxy {
	  create_instance $1 0 ${HAPROXY_NAME} 1
  }

  log_and_print "\n"
  log_and_print "################################################"
  log_and_print "## Create fleet instance files from templates ##"
  log_and_print "################################################"
  log_and_print "\n"

  create_mysql MYSQL_NUM_INSTANCES
  create_memcache MEMCACHE_NUM_INSTANCES
  create_mysql_demodata MYSQL_DEMODATA_NUM_INSTANCES
  create_apache APACHE_NUM_INSTANCES
  create_haproxy HAPROXY_NUM_INSTANCES

fi

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

  log_and_print Starting mysql instances
  log_and_print instances: ${INSTANCES[${MYSQL_NAME}]}
  fleetctl start ${INSTANCES[${MYSQL_NAME}]}
  log_and_print "\n"

  log_and_print Starting memcache instances
  fleetctl start ${INSTANCES[${MEMCACHE_NAME}]}
  log_and_print "\n"

  log_and_print Starting apache instances
  fleetctl start ${INSTANCES[${APACHE_NAME}]}
  log_and_print "\n"

  log_and_print Starting haproxy instances
  fleetctl start ${INSTANCES[${HAPROXY_NAME}]}
  log_and_print "\n"

  cd $EXEC_PATH
  log_and_print Running instances:
  fleetctl list-units | tee -a ${LOG_FILE_PATH}
fi