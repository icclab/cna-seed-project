#!/bin/bash

sed -i -e "s/^server\-id\s*\=\s.*$/server-id = ${RANDOM}/" /etc/mysql/my.cnf
