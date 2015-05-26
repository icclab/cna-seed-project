### Log-courier base image
This docker image is the base image for log-courier images. It provides the skeleton for specific log-courier images where you basically just need to modify the configuration file.

#### Content
This base image contains supervisord to start confd and log-courier. Confd and supervisord are already configured so images based on this image only need to provide a custom log-courier configuration file.

#### Service Discovery
Confd and etcd is used to listen to logstash instances. All logstash instances are written in the configuration file as servers.
The listening path in etcd is:
```
/services/logcollector
```

The following information is used:
```
/services/logcollector/$id/ip
/services/logcollector/$id/port
```

#### Write service specific log-courier docker image
To write your own log-courier docker image usually all you need to do is to modify the configuration file template.
Specify which log file(s) you want to ship under the `files` section and it should already work.

##### Log courier variables
In the configuration file for log-courier you can use some special variables which are replaced before the configuration is loaded by log-courier:
- %hostname% -> Hostname where the logs come from
- %service-id% -> service ID of the service

These variables get replaced when the container is started with the environment variables LOG_SRC_ID and LOG_SRC_HOSTNAME. 

```
docker run --name zurmo_log_courier_any \
        -e "ETCD_ENDPOINT=$(ip route | awk '/docker0/ {print $NF }'):4001" \
        -e "LOG_SRC_HOSTNAME=host1" \
        -e "LOG_SRC_ID=1" \
        --volumes-from name_of_the_service_container \
        icclabcna/zurmo_log_courier_any
```

#### Log cleanup
If there are logs in the path /var/log/collectd from collectd, they will be cleaned up by a cronjob run every day at 01:00.
This cleanup deletes files older than one day in the given path.
