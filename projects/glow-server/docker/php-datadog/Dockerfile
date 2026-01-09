FROM amazonlinux:2023.5.20240624.0 as build-grpc

RUN dnf -y update \
    && dnf install -y \
        php8.3 \
        php8.3-devel \
        php-pear \
        gcc \
        make \
        autoconf \
        automake \
        libtool \
        re2c \
        bison \
        which

RUN pecl install grpc
RUN cp $(php-config --extension-dir)/grpc.so /tmp/grpc.so

FROM amazonlinux:2023.5.20240624.0

ARG GIT_USER
ARG GIT_TOKEN
ARG GIT_SSH_KEY

RUN rpm --import https://repo.mysql.com/RPM-GPG-KEY-mysql-2022
RUN dnf -y localinstall https://dev.mysql.com/get/mysql80-community-release-el9-5.noarch.rpm

RUN dnf -y update \
    && dnf install -y \
        libicu-devel \
        libzip-devel \
        oniguruma-devel \
        libyaml-devel \
        git \
        unzip \
        graphviz \
        openssh-clients \
        mysql-community-client \
        libpng-devel \
        libjpeg-devel \
        freetype-devel \
        php8.3 \
        php8.3-devel \
        php-pear \
        php8.3-fpm \
        php8.3-intl \
        php8.3-pdo \
        php8.3-mysqlnd \
        php8.3-bcmath \
        php8.3-opcache \
        php8.3-gd

# pecl経由で軽量な拡張のみインストール
RUN pecl install \
    zip \
    xdebug \
    apcu \
    redis \
    yaml \
    protobuf

RUN echo "extension=zip.so" > /etc/php.d/00-zip.ini \
    && echo "extension=apcu.so" > /etc/php.d/00-apcu.ini \
    && echo "extension=redis.so" > /etc/php.d/00-redis.ini \
    && echo "extension=yaml.so" > /etc/php.d/00-yaml.ini \
    && echo "extension=protobuf.so" > /etc/php.d/00-protobuf.ini \
    && echo "extension=grpc.so" > /etc/php.d/00-grpc.ini

# grpc拡張のみビルドステージからコピー
COPY --from=build-grpc /tmp/grpc.so /usr/lib64/php8.3/modules/grpc.so

# xdebugは99-xdebug.iniに設定を記述

# php設定
COPY ./www.conf /etc/php-fpm.d/www.conf
COPY ./zzz-www.conf /etc/php-fpm.d/zzz-www.conf
COPY ./php.ini /etc/php.d/php.ini
COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www

RUN mkdir -p /run/php-fpm \
    && chown 775 /run/php-fpm

EXPOSE 9000

RUN ln -s /etc/rc.d/init.d /etc/init.d

# Install PHP Datadog tracer
RUN curl -LO https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php && \
    php datadog-setup.php --php-bin=all --enable-appsec --enable-profiling && \
    rm datadog-setup.php
COPY ./99-wp-ddtrace.ini /etc/php.d/99-wp-ddtrace.ini

CMD ["/usr/sbin/php-fpm", "--nodaemonize"]
