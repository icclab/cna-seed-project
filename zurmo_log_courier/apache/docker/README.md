### Log-courier for apache
This docker image is a log-courier service that ships apache logs to logstash instances.
Containers of this image are meant to be used in combination with an apache container that writes its logs to a directory shared as volume.

#### Log paths
The log-files for apache (access- and error-log) are expected to be in these paths:
```
/var/log/apache2/access.log
/var/log/apache2/error.log
```

Additionally a custom log-file of apache is expected to be written here:
```
/var/log/apache2/perf.log
```

And for the application logs:
```
/zurmo/zurmo/app/protected/runtime/application.log
/zurmo/zurmo/app/protected/runtime/sqlProfiling.log
/zurmo/zurmo/app/protected/runtime/memcacheProfiling.log
/zurmo/zurmo/app/protected/runtime/pageProfiling.log
```

#### Using the image
To start an apache log-courier container only makes sense if you already have an apache container. Start the apache container first and after it has started start this log courier container. Be aware of the following important parameters:

- You need to define how etcd is accessible from within the container. The example below uses a dynamic approach which uses the *docker0* address of the host and the (static) port *4001* as etcd endpoint.
- You need to mount the volumes from your apache container

```
docker run --name zurmo_log_courier_apache -e "ETCD_ENDPOINT=$(ip route | awk '/docker0/ {print $NF }'):4001" --volumes-from name_of_the_apache_container icclabcna/zurmo_log_courier_apache
```


