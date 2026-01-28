FROM amazonlinux:2023.5.20240624.0

ARG GIT_USER
ARG GIT_TOKEN
ARG GIT_SSH_KEY

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
        freetype-devel \
        jq \
        parallel \
        pigz \
        wget

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

# php設定
COPY ./docker/envs/ecs/php-admin/www.conf /etc/php-fpm.d/www.conf
COPY ./docker/envs/ecs/php-admin/zzz-www.conf /etc/php-fpm.d/zzz-www.conf
COPY ./docker/envs/ecs/php-admin/php.ini /etc/php.d/php.ini
COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www

RUN mkdir -p /run/php-fpm \
    && chown 775 /run/php-fpm

EXPOSE 9000

COPY --from=composer:2.5.4 /usr/bin/composer /usr/bin/composer
RUN chmod -R 777 /var/log
RUN mkdir -p /var/www/admin
RUN mkdir -p /var/www/api
RUN mkdir -p /var/www/share
RUN mkdir -p /var/local/lib
RUN ln -s /var/local/lib /var/www/lib
COPY ./admin /var/www/admin
COPY ./api /var/www/api
COPY ./share /var/www/share
# share/appとshare/databaseはシンボリックリンクだがテキストファイルになってしまうので実ファイルをコピー
RUN rm -f /var/www/share/app /var/www/share/database
COPY ./api/app /var/www/share/app
COPY ./api/database /var/www/share/database
COPY ./lib /var/local/lib
COPY ./docker/envs/ecs/php-admin/.env.admin /var/www/admin/.env
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN chmod -R 777 /var/www/admin/storage
RUN chmod -R 777 /var/www/admin/public
RUN chmod -R 777 /var/www/admin/bootstrap/cache
RUN cd /var/www/admin && composer install
WORKDIR /var/www

RUN sed -i \
    -e 's|error_log = /var/log/php-fpm/error.log|;error_log = /var/log/php-fpm/error.log|' \
    /etc/php-fpm.conf

# シェルスクリプトを実行してGitの認証設定を行う
COPY ./docker/envs/ecs/php-admin/setup_git.sh /tmp/setup_git.sh
COPY ./docker/envs/ecs/php-admin/ssh_config /tmp/ssh_config
RUN chmod +x /tmp/setup_git.sh \
    && /tmp/setup_git.sh "${GIT_USER}" "${GIT_TOKEN}" "${GIT_SSH_KEY}" \
    && rm /tmp/setup_git.sh /tmp/ssh_config

RUN git config --global user.email "${GIT_USER}@wonderpla.net" \
    && git config --global user.name "${GIT_USER}" \
    && git config --global --add safe.directory /var/www/storage/app/masterdata_csv \
    && cp ~/.gitconfig /etc/gitconfig \
    && chmod 666 /etc/gitconfig

# パッケージインストール用にnpmのセットアップ
RUN dnf install -y nodejs
RUN cd /var/www/admin && npm install && npm run build

# Datalake転送で必要なdumplingのインストール
COPY ./admin/scripts/install_dumpling.sh /tmp/install_dumpling.sh
RUN chmod +x /tmp/install_dumpling.sh \
    && /tmp/install_dumpling.sh \
    && rm /tmp/install_dumpling.sh

# Datalake転送で必要なmillerのインストール
COPY ./admin/scripts/install_miller.sh /tmp/install_miller.sh
RUN chmod +x /tmp/install_miller.sh \
    && /tmp/install_miller.sh \
    && rm /tmp/install_miller.sh

COPY ./docker/envs/ecs/php-admin/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
