#!/bin/bash

git clone git://github.com/elasticsearch/logstash-forwarder.git
cd logstash-forwarder
go build -o logstash-forwarder

apt-get install -y \
  build-essential \
  ruby
gem install pleaserun
make generate-init-script
cp -r build/etc /
mkdir /var/log/logstash-forwarder
cp logstash-forwarder.conf.example /etc/logstash-forwarder.conf
