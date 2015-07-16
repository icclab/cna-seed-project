#!/bin/bash

FLEET_FILES_URL=${DYNAMITE_FLEET_FILES_URL:-"https://raw.githubusercontent.com/icclab/cna-seed-project/master/init/fleet-files"}
DYNAMITE_CONFIG_URL=${DYNAMITE_CONFIG_URL:-"https://raw.githubusercontent.com/icclab/cna-seed-project/master/init/dynamite.yaml"}

DYNAMITE_BASE_DIRECTORY=/etc/dynamite
FLEET_FILE_LOCATION=${DYNAMITE_BASE_DIRECTORY}/fleet-files
DESCRIPTION_FILE_LOCATION=${DYNAMITE_BASE_DIRECTORY}/fleet_urls.txt
DYNAMITE_CONFIG_FILE_LOCATION=${DYNAMITE_BASE_DIRECTORY}/dynamite.yaml

URL_ENCODED_AT=%40
EXEC_PATH=`pwd`
LOG_FILE_PATH=`pwd`/debug.log

#create the directories for the template and instance files
mkdir -p ${DYNAMITE_BASE_DIRECTORY}
mkdir -p ${FLEET_FILE_LOCATION}

# comparing strings does ignore case
shopt -s nocasematch

# logs and prints to stdout the string provided
# param: as many you want
function log_and_print {
	CONTENT=$@
	echo -e $CONTENT | tee -a ${LOG_FILE_PATH}
}

# decodes the @ sign that was url encoded
# param: URL-encoded string
function url_decode_at {
	URL=$1
	echo ${URL//${URL_ENCODED_AT}/@}
}

# ----------------------
# Download the fleet files from github
# ----------------------

log_and_print "\n"
log_and_print "################################################"
log_and_print "## Download fleet files from github           ##"
log_and_print "################################################"
log_and_print "\n"

log_and_print "Delete previously used files"

if [ -d ${FLEET_FILE_LOCATION} ];
then
	rm ${FLEET_FILE_LOCATION}/*.service
	rm ${DYNAMITE_BASE_DIRECTORY}/*.txt
	rm ${DYNAMITE_BASE_DIRECTORY}/*.yaml
fi

log_and_print "Download description file ${FLEET_FILES_URL}"

curl ${FLEET_FILES_URL} > ${DESCRIPTION_FILE_LOCATION} 2>> ${LOG_FILE_PATH}

while read line
do
	log_and_print "Download fleet file from $line\n"
	file_name=${line##*/}
	file_name=$(url_decode_at $file_name)
	log_and_print "Extracted filename $file_name"
	curl $line > ${FLEET_FILE_LOCATION}/${file_name} 2>> ${LOG_FILE_PATH}
done < ${DESCRIPTION_FILE_LOCATION} 

rm ${DESCRIPTION_FILE_LOCATION}

log_and_print "Download dynamite configuration file ${DYNAMITE_CONFIG_URL}"

curl ${DYNAMITE_CONFIG_URL} > ${DYNAMITE_CONFIG_FILE_LOCATION} 2>> ${LOG_FILE_PATH}

