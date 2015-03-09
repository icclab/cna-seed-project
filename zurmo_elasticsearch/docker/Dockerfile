FROM dockerfile/elasticsearch

ADD https://github.com/kelseyhightower/confd/releases/download/v0.7.1/confd-0.7.1-linux-amd64 /usr/local/bin/confd
RUN chmod u+x /usr/local/bin/confd
RUN mkdir -p /etc/confd/conf.d && \
  mkdir -p /etc/confd/templates

ADD start.sh /start.sh

ADD elasticsearch.toml /etc/confd/conf.d/elasticsearch.toml
ADD elasticsearch.yml.tmpl /etc/conf.d/templates/elasticsearch.yml.tmpl

CMD /start.sh