# Docker image containing the Diamond collector
#
# VERSION               0.0.1

FROM      ubuntu:14.04

# Set locale
RUN locale-gen --no-purge en_US.UTF-8
ENV LC_ALL en_US.UTF-8

# Install dependencies
ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update && \
  apt-get install -y python-software-properties wget sudo net-tools
  apt-get update
  apt-get install -y vim  pbuilder python-mock python-configobj python-support cdbs python-psycopg2 git

RUN git clone https://github.com/ldoguin/Diamond/

WORKDIR /Diamond
RUN make builddeb && \
  sudo dpkg -i build/diamond_*_all.deb
ADD diamond /etc/diamond/

CMD exec /usr/bin/diamond  -f
