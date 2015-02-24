FROM ubuntu

RUN apt-get update && apt-get install -y \
  openjdk-7-jre
 
ADD logstash-1.4.2.tar.gz /logstash-1.4.2.tar.gz
ADD rsyslog.conf /etc/rsyslog.conf
ADD rsyslog.d_haproxy.conf /etc/rsyslog.d/haproxy.conf
RUN /usr/sbin/rsyslogd

CMD /logstash-1.4.2.tar.gz/logstash-1.4.2/bin/logstash agent -f /logstash.cfg