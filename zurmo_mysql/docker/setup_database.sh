#!/bin/bash

sed -i "s@ZURMO_PASSWORD@${MYSQL_ROOT_PASSWORD}@g" /create_user.sql

service mysql start
mysql < /create_db.sql
mysql < /create_user.sql
mysql zurmo < /zurmo.sql
service mysql stop

mysqld_safe
