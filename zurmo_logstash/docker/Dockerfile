FROM ubuntu:14.04

# install required packages
RUN apt-get update && apt-get install -y \
  openjdk-7-jre \
  make \
  git \
  rubygems-integration

ADD logstash-1.4.2.tar.gz /logstash-1.4.2.tar.gz

# install log-courier logstash plugin
ADD install_courier_plugin.sh /install_courier_plugin.sh
RUN /install_courier_plugin.sh 
 
ADD logstash_courier.cfg /logstash/logstash_courier.cfg

CMD /logstash-1.4.2.tar.gz/logstash-1.4.2/bin/logstash agent -f /logstash/logstash_courier.cfg
