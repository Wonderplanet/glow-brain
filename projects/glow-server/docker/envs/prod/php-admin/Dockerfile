FROM php:8.1-fpm-bullseye

#ARG GIT_USER
#ARG GIT_TOKEN
#ARG GIT_SSH_KEY

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev libonig-dev graphviz git openssh-client default-mysql-client
RUN docker-php-ext-install intl pdo_mysql zip bcmath opcache
RUN pecl install redis \
    && pecl install xdebug \
    && docker-php-ext-enable redis xdebug
COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer

# git周りの設定
RUN echo $(ls /tmp/docker)
COPY ./setup_git.sh /tmp/setup_git.sh
COPY ./ssh_config /tmp/ssh_config
RUN chmod +x /tmp/setup_git.sh \
    && /tmp/setup_git.sh "${GIT_USER}" "${GIT_TOKEN}" "${GIT_SSH_KEY}" \
    && rm /tmp/setup_git.sh /tmp/ssh_config

RUN git config --global user.email "${GIT_USER}@wonderpla.net" \
    && git config --global user.name "${GIT_USER}" \
    && git config --global --add safe.directory /var/www/storage/app/masterdata_csv \
    && cp ~/.gitconfig /etc/gitconfig \
    && chmod 666 /etc/gitconfig

WORKDIR /var/www
