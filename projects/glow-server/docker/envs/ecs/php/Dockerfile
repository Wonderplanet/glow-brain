FROM amazonlinux:2023.5.20240624.0

ARG GIT_USER
ARG GIT_TOKEN
ARG GIT_SSH_KEY

ARG MOMENTO_API_KEY

RUN rpm --import https://repo.mysql.com/RPM-GPG-KEY-mysql-2022

# Fedora36用のリポジトリだとlibcのバージョンが合わないためRHEL9
# https://dev.classmethod.jp/articles/install-mysql-client-to-amazon-linux-2023/
RUN dnf -y localinstall https://dev.mysql.com/get/mysql80-community-release-el9-5.noarch.rpm

# パッケージのインストール
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
        freetype-devel

# PHPと拡張インストール
RUN dnf -y install \
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

# pecl経由で拡張をインストール
RUN pecl install \
    zip \
    apcu \
    redis \
    yaml \
    protobuf \
    grpc
RUN echo "extension=zip.so" > /etc/php.d/00-zip.ini \
    && echo "extension=apcu.so" > /etc/php.d/00-apcu.ini \
    && echo "extension=redis.so" > /etc/php.d/00-redis.ini \
    && echo "extension=yaml.so" > /etc/php.d/00-yaml.ini \
    && echo "extension=protobuf.so" > /etc/php.d/00-protobuf.ini \
    && echo "extension=grpc.so" > /etc/php.d/00-grpc.ini
# xdebugは99-xdebug.iniに設定を記述

# php設定
COPY ./docker/envs/ecs/php/www.conf /etc/php-fpm.d/www.conf
COPY ./docker/envs/ecs/php/zzz-www.conf /etc/php-fpm.d/zzz-www.conf
COPY ./docker/envs/ecs/php/php.ini /etc/php.d/php.ini
COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www

RUN mkdir -p /run/php-fpm \
    && chown 775 /run/php-fpm

EXPOSE 9000

COPY --from=composer:2.5.4 /usr/bin/composer /usr/bin/composer
RUN chmod -R 777 /var/log
RUN mkdir -p /var/www/api
RUN mkdir -p /var/local/lib
COPY ./api /var/www/api
COPY ./lib /var/local/lib
COPY ./docker/envs/ecs/php/.env.api /var/www/api/.env
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN cd /var/www/api && composer install
RUN chmod -R 777 /var/www/api/storage
RUN chmod -R 777 /var/www/api/public
RUN chmod -R 777 /var/www/api/bootstrap/cache
WORKDIR /var/www

RUN sed -i \
    -e 's|error_log = /var/log/php-fpm/error.log|;error_log = /var/log/php-fpm/error.log|' \
    /etc/php-fpm.conf

RUN ln -s /etc/rc.d/init.d /etc/init.d

# Install PHP Datadog tracer
RUN curl -LO https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php && \
    php datadog-setup.php --php-bin=all --enable-appsec --enable-profiling && \
    rm datadog-setup.php
COPY ./docker/envs/ecs/php/99-wp-ddtrace.ini /etc/php.d/99-wp-ddtrace.ini

COPY ./docker/envs/ecs/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

COPY ./docker/envs/ecs/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["/usr/sbin/php-fpm", "--nodaemonize"]
