FROM nginx

RUN apt-get update && apt-get install -y \
  wget
RUN wget --no-check-certificate https://github.com/kelseyhightower/confd/releases/download/v0.7.1/confd-0.7.1-linux-amd64 && \
  chmod +x confd-0.7.1-linux-amd64 && \
  mv confd-0.7.1-linux-amd64 /usr/local/bin/confd && \
  mkdir -p /etc/confd/{conf.d,templates}

ADD kibana-3.1.2.tar.gz /usr/share/nginx/html
ADD kibana.tmpl /etc/confd/templates/
ADD kibana.toml /etc/confd/conf.d/
ADD start.sh /start.sh

CMD ["/start.sh"]