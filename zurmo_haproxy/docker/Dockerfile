FROM dockerfile/haproxy

MAINTAINER Sandorkan <brunnersandro@hotmail.com>

COPY haproxy.cfg /etc/haproxy/haproxy.cfg

# Install confd on the container
RUN wget https://github.com/kelseyhightower/confd/releases/download/v0.7.1/confd-0.7.1-linux-amd64
RUN mv confd-0.7.1-linux-amd64 /usr/local/bin/confd
RUN chmod u+x /usr/local/bin/confd
RUN mkdir -p /etc/confd/{conf.d,templates}

COPY haproxy.toml /etc/confd/conf.d/haproxy.toml
COPY haproxy.cfg.tmpl /etc/confd/templates/haproxy.cfg.tmpl

# rsyskig config for haproxy
COPY haproxy.conf /etc/rsyslog.d/haproxy.conf

COPY start-service /usr/local/bin/start-service
RUN chmod +x /usr/local/bin/start-service

EXPOSE 80 1936

CMD ["/usr/local/bin/start-service"]