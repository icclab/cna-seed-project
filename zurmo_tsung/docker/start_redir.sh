#!/bin/bash

#redir --lport=80 --laddr=127.0.0.1 --caddr=web --cport=80
#sed -Ei 's/LINK/$LINK/g' tsung_sample_config.xml

echo $LINK
echo "Link: ${LINK}"
sed -i "s@LINK@${LINK}@g" tsung_sample_config.xml
cat tsung_sample_config.xml
tsung -f tsung_sample_config.xml start
