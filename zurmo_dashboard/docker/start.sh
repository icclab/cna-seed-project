#!/bin/bash

cd /etcd-v2.0.5-linux-amd64

# watch for changes in /services in etcd
# if change detected -> write structure to /dashboard/current_structure.txt
# call program that translates etcd output to tree structure and save tree in /dashboard/graph.txt
./etcdctl exec-watch --recursive /services -- /bin/bash -c "/etcd-v2.0.5-linux-amd64/etcdctl ls / --recursive > /dashboard/current_structure.txt && /dashboard/etcd_tree_to_graph.sh /dashboard/current_structure.txt > /dashboard/graph.txt" &

# start the nodejs web server
nodejs /dashboard/server.js
