FROM ubuntu

ADD config /zurmo/zurmo/app/protected/config
ADD conf.d /etc/confd/conf.d
ADD templates /etc/confd/templates

RUN chown -R www-data /zurmo

VOLUME /zurmo/zurmo/app/protected/config
VOLUME /etc/confd/templates
VOLUME /etc/confd/conf.d