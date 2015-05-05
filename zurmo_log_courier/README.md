### Log-courier docker images
The folders in here represent the hierarchical structure of the log-courier docker images. The base folder contains the basic docker image that basically provides a template for concrete log-courier instances attached to specific services (e.g haproxy, apache, specific application).  
The other folders represent the specific docker images where the configuration of log-courier is adapted to the service.

#### Creating a new log-courier image
Just copy one of the existing folders (apache, haproxy, ...) and name it after your service you want to ship logs from.
Basically you just need to change the fleet file to name your container correctly. Additionally the log-courier template file has to be changed so that the correct log-file is shipped.

Make sure your container provides the log files on a volume because the log-courier container needs to access the log-files from the container the service (apache, haproxy...) runs in.

__Checklist:__
- Your service container (apache, haproxy ...) exposes the logs on a volume
- You modified the fleet file so that your container is named properly and binds the volumes from the service container
- You modified the log-courier.cfg.tmpl file and specified the path to the log-file which should be shipped


#### Why not logstash-forwarder?
The first try to forward logs to logstash was with logstash-forwarder. But the need to configure SSL-certificates for authentication was too much of a hassle. Either you need to generate a certificate with all ip's of your logstash-forwarders in it or you use a *.anything approach where you need to use hostnames. The former is not very well suited for dynamic environments where logstash-forwarders can be added/removed dynamically. You would have the certificates every time a
logstash-forwarder is removed/added and distribute it to all of them plus the logstash instances. The latter could be configured but you'll have to use a DNS service or make some hacks with the /etc/hosts file.  
To circumvent these issues we just decided to use log-courier instead.
