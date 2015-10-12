# FROM dockerfile/haproxy
  # dockerfile/haproxy is not available on dockerhub anymore
  # the following lines were taken directly from "dockerfile/haproxy" and "dockerfile/ubuntu" github repo

  # from dockerfile/ubuntu
FROM ubuntu:14.04

# Install.
RUN \
  sed -i 's/# \(.*multiverse$\)/\1/g' /etc/apt/sources.list && \
  apt-get update && \
  apt-get -y upgrade && \
  apt-get install -y build-essential && \
  apt-get install -y software-properties-common && \
  apt-get install -y byobu curl git htop man unzip vim wget && \
  rm -rf /var/lib/apt/lists/*

# Add files.
ADD root/.bashrc /root/.bashrc
ADD root/.gitconfig /root/.gitconfig
ADD root/.scripts /root/.scripts

# Set environment variables.
ENV HOME /root

# Define working directory.
WORKDIR /root

# Define default command.
# CMD ["bash"]

  # from dockerfile/haproxy

# Install Haproxy.
RUN \
  sed -i 's/^# \(.*-backports\s\)/\1/g' /etc/apt/sources.list && \
  apt-get update && \
  apt-get install -y haproxy=1.5.3-1~ubuntu14.04.1 && \
  sed -i 's/^ENABLED=.*/ENABLED=1/' /etc/default/haproxy && \
  rm -rf /var/lib/apt/lists/*


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

# COPY haproxy-etcd-service-announcer /usr/local/bin/haproxy-etcd-service-announcer
# RUN chmod +x /usr/local/bin/haproxy-etcd-service-announcer

EXPOSE 80 1936

VOLUME /var/log
# VOLUME /logstash_forwarder

CMD ["/usr/local/bin/start-service"]
