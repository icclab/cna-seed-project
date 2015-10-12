#!/bin/bash

#
# 
# Original work Copyright 2015 Patrick Galbraith 
# Modified work Copyright 2015 Giovanni Toffetti

# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
# 
#    http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#
#
# 
# I am not particularly fond of this script as I would prefer 
# using confd to do this ugly work. Confd functionality is being
# built into kubernetes as I write this which may replace this
# 
# also important here is that this script will work outside of 
# Kubernetes as long as the container is run with the correct 
# environment variables passed to replace discovery that 
# Kubernetes provides
# 
set -vx

HOSTNAME=`hostname`
export tempSqlFile='/tmp/mysql-first-time.sql'

if [ "${1:0:1}" = '-' ]; then
  set -- mysqld "$@"
fi

if [ "$1" = 'mysqld' ]; then
  # read DATADIR from the MySQL config
  DATADIR="$("$@" --verbose --help 2>/dev/null | awk '$1 == "datadir" { print $2; exit }')"
  
  export MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD:-zurmo}

  if [ ! -d "$DATADIR/mysql" ]; then
    if [ -z "$MYSQL_ROOT_PASSWORD" -a -z "$MYSQL_ALLOW_EMPTY_PASSWORD" ]; then
      echo >&2 'error: database is uninitialized and MYSQL_ROOT_PASSWORD not set'
      echo >&2 '  Did you forget to add -e MYSQL_ROOT_PASSWORD=... ?'
      exit 1
    fi

    chown -R mysql:mysql "$DATADIR"

    echo 'Running mysql_install_db ...'
        mysql_install_db --datadir="$DATADIR"
        echo 'Finished mysql_install_db'

    
    # These statements _must_ be on individual lines, and _must_ end with
    # semicolons (no line breaks or comments are permitted).
    # TODO proper SQL escaping on ALL the things D:
    
    
    cat > "$tempSqlFile" <<-EOSQL
DELETE FROM mysql.user ;
CREATE USER 'root'@'%' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}' ;
GRANT ALL ON *.* TO 'root'@'%' WITH GRANT OPTION ;
DROP DATABASE IF EXISTS test ;
EOSQL
    export MYSQL_DATABASE=${MYSQL_DATABASE:-zurmo}
    if [ "$MYSQL_DATABASE" ]; then
      echo "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DATABASE\` ;" >> "$tempSqlFile"
    fi
    export MYSQL_USER=${MYSQL_USER:-zurmo}
    export MYSQL_PASSWORD=${MYSQL_PASSWORD:-zurmo}
    if [ "$MYSQL_USER" -a "$MYSQL_PASSWORD" ]; then
      echo "CREATE USER '$MYSQL_USER'@'%' IDENTIFIED BY '$MYSQL_PASSWORD' ;" >> "$tempSqlFile"
      
      if [ "$MYSQL_DATABASE" ]; then
        echo "GRANT ALL ON \`$MYSQL_DATABASE\`.* TO '$MYSQL_USER'@'%' ;" >> "$tempSqlFile"
      fi
    fi
    export GALERA_CLUSTER=${GALERA_CLUSTER:-true}
    export WSREP_SST_PASSWORD=${WSREP_SST_PASSWORD:-zurmo}
    if [ -n "$GALERA_CLUSTER" -a "$GALERA_CLUSTER" = true ]; then
      WSREP_SST_USER=${WSREP_SST_USER:-"sst"}
      if [ -z "$WSREP_SST_PASSWORD" ]; then
        echo >&2 'error: database is uninitialized and WSREP_SST_PASSWORD not set'
        echo >&2 '  Did you forget to add -e WSREP_SST_PASSWORD=xxx ?'
        exit 1
      fi

      sed -i -e "s|wsrep_sst_auth \= \"sstuser:changethis\"|wsrep_sst_auth = ${WSREP_SST_USER}:${WSREP_SST_PASSWORD}|" /etc/confd/mysql/templates/cluster.cnf.tmpl

      export COREOS_PRIVATE_IPV4=${COREOS_PRIVATE_IPV4:-172.17.42.1}

      WSREP_NODE_ADDRESS=${COREOS_PRIVATE_IPV4}
      if [ -n "$WSREP_NODE_ADDRESS" ]; then
        sed -i -e "s|^#wsrep_node_address \= .*$|wsrep_node_address = ${WSREP_NODE_ADDRESS}|" /etc/confd/mysql/templates/cluster.cnf.tmpl
      fi

      echo "CREATE USER '${WSREP_SST_USER}'@'localhost' IDENTIFIED BY '${WSREP_SST_PASSWORD}';" >> "$tempSqlFile"
      echo "GRANT RELOAD, LOCK TABLES, REPLICATION CLIENT ON *.* TO '${WSREP_SST_USER}'@'localhost';" >> "$tempSqlFile"
    fi
    echo 'FLUSH PRIVILEGES ;' >> "$tempSqlFile"
    
    set -- "$@" --init-file="$tempSqlFile"
  fi
  
  
fi

export ETCD_ENDPOINT=${ETCD_ENDPOINT:-172.17.42.1:4001}
# Check if cluster  has been previously successfully initialized
export INITIALIZED=`curl -X GET http://$ETCD_ENDPOINT/v2/keys/zurmo_galera/initialized | grep -o 'true'`
# if initialized or we are a node with id > 1 then rejoin the cluster
if [ \( -n "$INITIALIZED" -a "$INITIALIZED" = true \) -o ! \( $GALERA_CLUSTER_NODE_ID = 1 \) ]; then
     # Try to make initial configuration every 5 seconds until successful
    until confd -verbose -debug -onetime -node $ETCD_ENDPOINT -config-file /etc/confd/mysql/conf.d/zurmo_galera_cluster.toml; do
      echo "[mysql-cluster] waiting for confd to create initial mysql-cluster configuration."
      sleep 5
    done    
    echo "[mysql-cluster] mysql-cluster configuration is now:"
    cat /etc/mysql/conf.d/cluster.cnf
    exec "$@"
# we are primary node (node_id=1)
else
    # begin with empty gcomm URL (primary node)    
    WSREP_CLUSTER_ADDRESS="gcomm://"
    cp /etc/confd/mysql/templates/cluster.cnf.tmpl /etc/mysql/conf.d/cluster.cnf
    sed -i -e "s|^wsrep_cluster_address \= .*$|wsrep_cluster_address = ${WSREP_CLUSTER_ADDRESS}|" /etc/mysql/conf.d/cluster.cnf
    # set -new cluster
    set -- "$@" --wsrep-new-cluster
    echo "[mysql-galera] mysql-galera configuration is now:"
    cat /etc/mysql/conf.d/cluster.cnf
    # double check if INIT set to true
    if [ "$INIT_ZURMO_DB" == true ]; then
        # start in background
        exec "$@" &
        # wait for the cluster to have at least 3 nodes
        # wait for cluster to reach size 3
        until [ $(mysql --password=$MYSQL_ROOT_PASSWORD < check_cluster_size.sql | tail -n 1 | cut -f 2) -gt 2 ]; do 
          echo "Waiting for cluster to reach size 3" 
          sleep 5 
        done
        # double check that DB has not yet been initialized
        if  [ $(mysql --password=$MYSQL_ROOT_PASSWORD $MYSQL_DATABASE -e "show tables;" | wc -l) -eq 0 ]; then
          echo "Database tables do not exist. Initializing database"
          mysql --password=$MYSQL_ROOT_PASSWORD $MYSQL_DATABASE < /zurmo.sql
        fi
        # use etcd / curl to set the database to status initialized
        curl -X PUT http://$ETCD_ENDPOINT/v2/keys/zurmo_galera/initialized -d value=true
        wait $!
    else
        exec "$@"
    fi
fi 

# If we get here it's because the initial mysql process has exited but the cluster should have been initialized
# now we can do discovery and we'll update the cluster conf, but we won't restart mysqld, mysqld_safe will do that for us
## check for changes every 10 seconds
confd -interval 10 -node $ETCD_ENDPOINT -config-file /etc/confd/mysql/conf.d/zurmo_galera_cluster.toml &
echo "[mysql-cluster] confd is now monitoring etcd for changes"
mysqld_safe

