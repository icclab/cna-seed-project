### Log-courier for haproxy 
This docker image is a log-courier service that ships haproxy logs to logstash instances.
Containers of this image are meant to be used in combination with an haproxy container that writes its logs to a directory shared as volume.

#### Log paths
The log-file for haproxy is expected to be in this path:
```
/var/log/haproxy.log
```

#### Using the image
To start an haproxy log-courier container only makes sense if you already have an haproxy container. Start the haproxy container first and after it has started start this log courier container. Be aware of the following important parameters:

- You need to define how etcd is accessible from within the container. The example below uses a dynamic approach which uses the *docker0* address of the host and the (static) port *4001* as etcd endpoint.
- You need to mount the volumes from your haproxy container

```
docker run --name zurmo_log_courier_haproxy -e "ETCD_ENDPOINT=$(ip route | awk '/docker0/ {print $NF }'):4001" --volumes-from name_of_the_haproxy_container icclabcna/zurmo_log_courier_haproxy
```


