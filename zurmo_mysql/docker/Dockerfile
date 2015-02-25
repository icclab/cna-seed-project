FROM dockerfile/mysql
ADD my.cnf /etc/mysql/my.cnf
ADD setup_database.sh /
ADD *.sql /
RUN chmod +x /setup_database.sh
EXPOSE 3306
CMD /bin/bash /setup_database.sh