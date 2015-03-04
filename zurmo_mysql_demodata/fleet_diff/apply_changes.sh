#!/bin/bash

# replaces the image name to use the tag :demodata and rename service file
sed -i "s/icclabcna\/zurmo_mysql/icclabcna\/zurmo_mysql:demodata/g" ../fleet/zurmo_mysql@.service
sed -i "s/zurmo_mysql_discovery/zurmo_mysql_demodata_discovery/g" ../fleet/zurmo_mysql@.service
mv ../fleet/zurmo_mysql@.service ../fleet/zurmo_mysql_demodata@.service

# replaces the referenced image of the sidekick (add _demodata to name) and rename service file
sed -i "s/zurmo_mysql@%i/zurmo_mysql_demodata@%i/g" ../fleet/zurmo_mysql_discovery@.service
mv ../fleet/zurmo_mysql_discovery@.service ../fleet/zurmo_mysql_demodata_discovery@.service