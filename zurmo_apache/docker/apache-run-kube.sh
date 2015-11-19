#!/bin/bash
. /etc/apache2/envvars

set -eo pipefail

collectd -C /collectd.conf

# set file permissions of www-directory
#/set_data_permissions.sh
chown -R www-data /zurmo

exec dockerize \
     -stdout /var/log/apache2/access.log \
     -stderr /var/log/apache2/error.log \
     /usr/sbin/apache2 -DFOREGROUND
