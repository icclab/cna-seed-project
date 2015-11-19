# Kube cluster on Openstack

Following the guide from here: 
https://github.com/kubernetes/kubernetes/blob/master/docs/getting-started-guides/coreos/coreos_multinode_cluster.md

## create master kube node
```nova boot --image CoreOS_723.3.0 --key-name cna_key --flavor m1.small --nic net-id=c666051f-5d3b-4705-b8a2-00a9f3e7a0b8 --security-group kubernetes --user-data $PWD/kubernetes/docs/getting-started-guides/coreos/cloud-configs/master.yaml kube-master```

## modify node.yaml to use master nodes's private IP
edit node.yaml -> saved as node_lisa.yaml

## add a minion
```nova boot --image CoreOS_723.3.0 --key-name cna_key --flavor m1.small --nic net-id=c666051f-5d3b-4705-b8a2-00a9f3e7a0b8 --security-group kubernetes --user-data node_lisa.yaml minion01```

~~## Make sure docker starts containers using the flannel network~~
~~See: https://www.digitalocean.com/community/tutorials/how-to-install-and-configure-kubernetes-on-top-of-a-coreos-cluster~~
~~### stop the Docker bridge (this should allow docker to restart using the flannel one)~~
~~sudo /usr/bin/ip link set dev docker0 down~~
~~sudo /usr/sbin/brctl delbr docker0~~
~~### restart docker.service~~
~~sudo systemctl restart docker.service~~
The docker.service dropin in node_lisa.yaml takes care of starting docker after flannel and getting rid of docker0 bridge.
(The docker0 bridge prevented docker from starting using the flannel net)

## play with security group
My minion was not able to access the API server on port 8080 on master node.
I had to specify an ingress rule on port 8080 from kubernetes security gorup.
Finally I opened all ports among VMs in the same group, this fixed kubectl logs...

## Adding DNS
Following guide here: https://github.com/kubernetes/kubernetes/tree/master/cluster/addons/dns (CoreOS is not a supported env, so it has to be done manually)
Ended up using kube-dns from latest repo:

kubectl create -f skydns-svc.yaml
kubectl create -f skydns-rc.yaml

Had to force DNS service IP in the service yaml, and explicitly give kube API endpoint in rc yaml.

Using nslook with busybox works with the following resolv.conf:

nameserver 10.100.75.254
search cluster.local

Example:
nslookup zurmo-allinone-service.default.svc
Server:    10.100.75.254
Address 1: 10.100.75.254

Name:      zurmo-allinone-service.default.svc
Address 1: 10.100.200.183


Also had to update node_lisa.yaml starting kubelet with DNS and domain.
This allows pods to find other pods by name, without needing to fiddle with resolv.conf in containers.


# Other notes:

## Fix container's addressing from within
 docker exec -it 0719b1b0da39 bash

## Add another cluster to be used by kubectl
kubectl config set-cluster openshift3 --server=https://127.0.0.1:8443 --insecure-skip-tls-verify=true
## switch to cluster
kubectl config set-context openshift3 --cluster=openshift3
kubectl config use-context openshift3

