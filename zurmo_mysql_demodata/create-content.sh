#!/bin/bash

# This script copies all docker and fleet files from the zurmo_mysql folder and
# at the end writes the files from the docker_diff folder to the docker and 
# files from the fleet_diff to the fleet folder

rm -r docker
rm -r fleet

cp -r ../zurmo_mysql/docker .
cp -r ../zurmo_mysql/fleet .
cp -r docker_diff/* docker/
cp -r fleet_diff/* fleet/

cd fleet_diff
./apply_changes.sh
cd ..

# cleanup
rm fleet/apply_changes.sh
