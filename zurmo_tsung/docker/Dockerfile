FROM ubuntu

RUN apt-get update && \
    apt-get install -y \
    erlang-dev \
    erlang-nox \
    make \
    wget \
    redir

ADD install_tsung.sh /install_tsung.sh
ADD start_redir.sh /start_redir.sh
ADD tsung_sample_config.xml /tsung_sample_config.xml
ADD zurmo_userdb.txt /zurmo_userdb.txt
RUN /install_tsung.sh
CMD /start_redir.sh
    
EXPOSE 80
