#!/bin/bash


if [ $isPC == "true" ]
then
#service mysql start --wsrep_provider=/usr/lib/galera/libgalera_smm.so --wsrep_cluster_address=gcomm://172.17.0.3:4567 --wsrep_sst_method=rsync --wsrep_cluster_name=galera_cluster --addresswsrep_node_address=172.17.0.5

# --wsrep_provider=/usr/lib/galera/libgalera_smm.so --wsrep_cluster_address=gcomm://172.17.0.5:4567 --wsrep_sst_method=rsync --wsrep_cluster_name=galera_cluster --wsrep_node_address=172.17.0.3

  mysqld
  mysql < /create_db.sql
  mysql < /create_user.sql
  mysql zurmo < /zurmo.sql
  service mysql stop

  mysqld_safe --wsrep-new-cluster

else 

  # Try to make initial configuration every 5 seconds until successful
  until confd -onetime -node $ETCD_ENDPOINT -config-file /etc/confd/mysql/my.cnf.tmpl; do
      echo "[mysql-cluster] waiting for confd to create initial mysql-cluster configuration."
      sleep 5
  done

  mysqld_safe

fi

echo "[mysql-cluster] mysql-cluster configuration is now:"
cat /etc/my.cnf

# Put a continual polling `confd` process into the background to watch
# for changes every 10 seconds
confd -interval 10 -node $ETCD_ENDPOINT -config-file /etc/confd/mysql/zurmo_mysql_cluster.toml &
echo "[mysql-cluster] confd is now monitoring etcd for changes..."

tail -f /create_user.sql

