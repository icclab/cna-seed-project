FROM dockerfile/elasticsearch

ADD start.sh /start.sh
ADD elasticsearch.toml /etc/confd/conf.d/elasticsearch.toml
ADD elasticsearch.yml.tmpl /etc/conf.d/templates/elasticsearch.yml.tmpl

CMD /start.sh