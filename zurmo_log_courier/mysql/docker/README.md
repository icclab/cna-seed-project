### Log-courier for mysql 
This docker image is a log-courier service that ships mysql logs to logstash instances.
Containers of this image are meant to be used in combination with a mysql container that writes its logs to a directory shared as volume.

#### Log paths
The log-file for mysql (error-log) is expected to be in this path:
```
/var/log/mysql/error.log
```

#### Using the image
To start a mysql log-courier container only makes sense if you already have a mysql container. Start the mysql container first and after it has started start this log courier container. Be aware of the following important parameters:

- You need to define how etcd is accessible from within the container. The example below uses a dynamic approach which uses the *docker0* address of the host and the (static) port *4001* as etcd endpoint.
- You need to mount the volumes from your mysql container

```
docker run --name zurmo_log_courier_mysql -e "ETCD_ENDPOINT=$(ip route | awk '/docker0/ {print $NF }'):4001" --volumes-from name_of_the_mysql_container icclabcna/zurmo_log_courier_mysql
```


