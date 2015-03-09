FROM ubuntu

RUN apt-get update && apt-get install -y \
  openjdk-7-jre
 
ADD logstash-1.4.2.tar.gz /logstash-1.4.2.tar.gz

ADD lumberjack.crt /logstash/lumberjack.crt
ADD lumberjack.key /logstash/lumberjack.key
ADD logstash_lumberjack.cfg /logstash/logstash_lumberjack.cfg

CMD /logstash-1.4.2.tar.gz/logstash-1.4.2/bin/logstash agent -f /logstash/logstash.cfg