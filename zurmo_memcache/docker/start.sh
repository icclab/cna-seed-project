#!/bin/bash

touch /var/log/memcached/memcached.log
tail -F /var/log/memcached/memcached.log & 
memcached -vv > /var/log/memcached/memcached.log 2>&1
