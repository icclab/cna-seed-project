FROM ubuntu

ADD config /zurmo/zurmo/app/protected/config
ADD conf.d /etc/confd/conf.d
ADD templates /etc/confd/templates

VOLUME /zurmo/zurmo/app/protected/config
VOLUME /etc/confd/templates
VOLUME /etc/confd/conf.d