FROM ubuntu:14.04

RUN apt-get update && apt-get install -y \
    golang \
    git \
    make \
    wget && \
  git clone https://github.com/driskell/log-courier && \
  cd log-courier && \
  make && \
  cp /log-courier/bin/log-courier /bin/ 

RUN wget --no-check-certificate https://github.com/kelseyhightower/confd/releases/download/v0.7.1/confd-0.7.1-linux-amd64 && \
  chmod +x confd-0.7.1-linux-amd64 && \
  mv confd-0.7.1-linux-amd64 /usr/local/bin/confd && \
  mkdir -p /etc/confd/conf.d && mkdir -p /etc/confd/templates

ADD log-courier.conf /etc/log-courier.conf
ADD start.sh /start.sh
RUN chmod +x /start.sh

CMD /start.sh
