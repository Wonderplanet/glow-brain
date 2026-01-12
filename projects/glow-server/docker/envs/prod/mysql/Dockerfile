FROM mysql:8.1.0 

ARG DB_PASS

ENV LANG=ja_JP.UTF-8
ENV MYSQL_ROOT_PASSWORD=${DB_PASS}

COPY ./docker/mysql/log.cnf /etc/mysql/conf.d/my.cnf
