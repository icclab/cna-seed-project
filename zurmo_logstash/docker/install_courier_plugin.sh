#!/bin/bash

wget https://github.com/driskell/log-courier/archive/v1.6.tar.gz
tar -zxvf v1.6.tar.gz
cd log-courier-1.6/
make gem
cd /logstash-1.4.2.tar.gz/logstash-1.4.2 
export GEM_HOME=vendor/bundle/jruby/1.9
java -jar vendor/jar/jruby-complete-1.7.11.jar -S gem install /log-courier-1.6/log-courier-1.6*.gem
cd /log-courier-1.6
cp -rvf lib/logstash /logstash-1.4.2.tar.gz/logstash-1.4.2/lib/
