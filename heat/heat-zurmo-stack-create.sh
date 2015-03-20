#!/bin/bash

############################################
# run this script via the 'source' command #
############################################

export ETCD_ENDPOINT=`curl https://discovery.etcd.io/new?size=3`
heat stack-create brnr_test_stack --template-url https://raw.githubusercontent.com/icclab/cna-seed-project/master/heat/coreos-heat.yaml --parameters "Number of Web Servers=3;Number of Cache Servers=3;CoreOS Cluster Discovery URL=$ETCD_ENDPOINT;Preload Docker Images=True;Zurmo Start Fleet Services=True"
