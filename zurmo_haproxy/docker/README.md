### HAProxy Docker-Container with confd
This container comes with a confd-service which will listen
for the change of etcd resources located at "/services/webserver" update the haproxy config accordingly and reload the haproxy service after a change has occured

It furthermore announces itself in etcd at the path --> /services/loadbalancer/<ip:port> 

This container was created with the intention of running in a CoreOS cluster

#### Get image from docker-repository
```
docker pull icclabcna/zurmo_haproxy
```

#### Run container
Make sure to pass the correct ETCD_ENDPOINT (IP-Address of Docker0 Interface / ETCD-Port) and private IP-address of the host where the container is running

```
docker run --rm --name loadbalancer -p 80:80 -p 1936:1936 -e "ETCD_ENDPOINT=${ETCD_ENDPOINT}" -e "HOST_PRIVATE_IPV4=${COREOS_PRIVATE_IPV4}" icclabcna/zurmo_haproxy
```

#### Default etcd endpoint
```
10.1.42.1:4001
```

This can be set in the 'start-service' script directly
or via environment variables. Check out the 'start\-service'
file

#### How to add or remove webservers to etcd

```
etcdctl set /services/webserver/172.16.0.1:80 172.16.0.1:80
```
```
etcdctl rm /services/webserver/172.16.0.1:80
```

#### How to build new docker images based on Dockerfile
```
docker build -t <username>/<image-name> .
```

#### Additional Information
[Digital Ocean: How To Use Confd and Etcd to Dynamically Reconfigure Services in CoreOS](https://www.digitalocean.com/community/tutorials/how-to-use-confd-and-etcd-to-dynamically-reconfigure-services-in-coreos)