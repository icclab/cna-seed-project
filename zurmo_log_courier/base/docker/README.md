= Log-courier base image
This docker image is the base image for log-courier images. It provides the skeleton for specific log-courier images where you basically just need to modify the configuration file.

== Service Discovery
Confd and etcd is used to listen to logstash instances. All logstash instances are written in the configuration file as servers.
The listening path in etcd is:
```
/services/logcollector
```

The following informations are used:
```
/services/logcollector/$id/ip
/services/logcollector/$id/port
```

== Write service specific log-courier docker image
To write your own log-courier docker image usually all you need to do is to modify the configuration file template.
Specify which log file(s) you want to ship under the `files` section and it should already work.
