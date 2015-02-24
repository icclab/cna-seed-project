### HAProxy Docker-Container with confd
This container comes with a confd-service which will listen
for the change of etcd resources located at "/services/webserver" update the haproxy config accordingly
and reload the haproxy service after a change has occured

#### Get image from docker-repository
```
# docker pull sandorkan/haproxy
```

#### Run container
```
# docker run --name haproxy sandorkan/haproxy
```

#### Default etcd endpoint
```
10.1.42.1:4001
```

This can be set in the 'start_service' script directly
or via environment variables. Check out the 'start\_service'
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
# docker build -t <username>/<image-name> .
```

#### Additional Information
[Digital Ocean: How To Use Confd and Etcd to Dynamically Reconfigure Services in CoreOS](https://www.digitalocean.com/community/tutorials/how-to-use-confd-and-etcd-to-dynamically-reconfigure-services-in-coreos)