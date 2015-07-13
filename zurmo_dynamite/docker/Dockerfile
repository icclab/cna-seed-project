FROM ubuntu:14.04 

RUN apt-get update && apt-get install -yq python3 \
	git \
	curl \
	python3-setuptools \
	python3-pip \
	libssl-dev \
	libffi-dev \
	rabbitmq-server \
	supervisor 

RUN git clone https://github.com/icclab/dynamite.git \
	&& cd dynamite \
	&& ./setup.py install \
	&& cd dist \
	&& easy_install dynamite*.egg \
	&& mkdir /etc/dynamite \
	&& mkdir /var/log/dynamite # Cache refresh @ 20150713.1416
	
ADD supervisord/supervisord.conf /etc/supervisor/supervisord.conf
ADD supervisord/kill_supervisor.py /usr/bin/kill_supervisor.py
ADD supervisord/dynamite_supervisord.conf /etc/supervisor/conf.d/dynamite_supervisord.conf
ADD start_dynamite.sh /start_dynamite.sh
ADD download_files.sh /download_files.sh
ADD startup.sh /startup.sh
ADD logging.conf /logging.conf

CMD /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
