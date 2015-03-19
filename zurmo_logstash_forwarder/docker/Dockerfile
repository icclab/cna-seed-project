FROM ubuntu:14.04

RUN apt-get update && apt-get install -y \
    golang \
    git \
    build-essential \
    ruby && \
  git clone git://github.com/elasticsearch/logstash-forwarder.git && \
  cd logstash-forwarder && \
  go build -o logstash-forwarder && \
  gem install pleaserun && \
  make generate-init-script && \
  cp -r build/etc / && \
  mkdir /var/log/logstash-forwarder && \
  cp logstash-forwarder.conf.example /etc/logstash-forwarder.conf

ADD start.sh /start.sh
RUN chmod +x /start.sh
ADD lumberjack.crt /logstash-forwarder/lumberjack.crt

CMD /start.sh