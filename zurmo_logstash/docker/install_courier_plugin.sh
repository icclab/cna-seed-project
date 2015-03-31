#!/bin/bash

git clone https://github.com/driskell/log-courier
cd log-courier
make gem
cd /logstash-1.4.2.tar.gz/logstash-1.4.2
export GEM_HOME=vendor/bundle/jruby/1.9
java -jar vendor/jar/jruby-complete-1.7.11.jar -S gem install /log-courier/log-courier-1.6*.gem
cd /log-courier
cp -rvf lib/logstash /logstash-1.4.2.tar.gz/logstash-1.4.2/lib/
