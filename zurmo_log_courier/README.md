= Log-courier docker images
The folders in here represent the hierarchical structure of the log-courier docker images. The base folder contains the basic docker image 
that basically provides a template for concrete log-courier instances attached to specific services (e.g haproxy, apache, specific application).  
The other folders represent the specific docker images where the configuration of log-courier is adapted to the service.


== Why not logstash-forwarder
The first try to forward logs to logstash was with logstash-forwarder. But the need to configure SSL-certificates for authentication was too much of a hassle.  
Either you need to generate a certificate with all ip's of your logstash-forwarders in it or you use a *.anything approach where you need to use hostnames.
The former is not very well suited for dynamic environments where logstash-forwarders can be added/removed dynamically. You would have the certificates every time a
logstash-forwarder is removed/added and distribute it to all of them plus the logstash instances. The latter could be configured but you'll have to use a DNS
service or make some hacks with the /etc/hosts file.  
To circumvent these issues we just decided to use log-courier instead.
