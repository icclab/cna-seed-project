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

It can also ship system metrics collected and written by collectd (csv plugin).
Currently supported are the plugins  
- cpu
- load
- Memory
- Disk
- DF
- Processes
- Swap
- Uptime

Collectd needs to write to the path which also has to be a volume:
```
/var/log/collectd
```

#### Log configuration variables
In the configuration file for log-courier you can use some special variables which are replaced before the configuration is loaded by log-courier:
- %hostname% -> Hostname where the logs come from
- %service-id% -> service ID of the service

These variables get replaced when the container is started with the environment variables LOG_SRC_ID and LOG_SRC_HOSTNAME. This logic is implemented in the base image.

#### Using the image
To start an apache log-courier container only makes sense if you already have an apache container. Start the apache container first and after it has started start this log courier container. Be aware of the following important parameters:

- You need to define how etcd is accessible from within the container. The example below uses a dynamic approach which uses the *docker0* address of the host and the (static) port *4001* as etcd endpoint.
- You need to mount the volumes from your apache container
- (Optional) LOG_SRC_HOSTNAME is the hostname of the container whose logs are forwarded 
- (Optional) LOG_SRC_ID is the service-id of the service being monitored
```
docker run --name zurmo_log_courier_apache \
	-e "ETCD_ENDPOINT=$(ip route | awk '/docker0/ {print $NF }'):4001" \
   	-e "LOG_SRC_HOSTNAME=zurmo_apache" \
        -e "LOG_SRC_ID=1" \
	--volumes-from name_of_the_apache_container \
	icclabcna/zurmo_log_courier_apache
```


