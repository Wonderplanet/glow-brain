FROM amazonlinux:2023.1.20230719.0

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
        graphviz

# PHPと拡張インストール
RUN dnf -y install \
    php8.1 \
    php8.1-devel \
    php-pear \
    php8.1-fpm \
    php8.1-intl \
    php8.1-pdo \
    php8.1-mysqlnd \
    php8.1-bcmath \
    php8.1-opcache

# pecl経由で拡張をインストール
RUN pecl install \
    zip \
    apcu \
    redis \
    yaml
RUN echo "extension=zip.so" > /etc/php.d/00-zip.ini \
    && echo "extension=apcu.so" > /etc/php.d/00-apcu.ini \
    && echo "extension=redis.so" > /etc/php.d/00-redis.ini \
    && echo "extension=yaml.so" > /etc/php.d/00-yaml.ini

COPY ./docker/php/www.conf /etc/php-fpm.d/www.conf
COPY ./docker/php/zzz-www.conf /etc/php-fpm.d/zzz-www.conf
COPY ./docker/php/opcache.ini /etc/php.d/opcache.ini
COPY ./docker/php/php.ini /etc/php.d/php.ini

RUN mkdir -p /run/php-fpm \
    && chown 775 /run/php-fpm

EXPOSE 9000

COPY --from=composer:2.5.4 /usr/bin/composer /usr/bin/composer
RUN chmod -R 777 /var/log
RUN mkdir -p /var/www/api
#RUN mkdir -p /var/www/lib
COPY ./api /var/www/api
#COPY ./lib /var/www/lib
COPY ./docker/php/.env.api /var/www/api/.env
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN cd /var/www/api && composer install
RUN chmod -R 777 /var/www/api/storage
RUN chmod -R 777 /var/www/api/public
RUN chmod -R 777 /var/www/api/bootstrap/cache
WORKDIR /var/www

CMD ["/usr/sbin/php-fpm", "--nodaemonize"]
