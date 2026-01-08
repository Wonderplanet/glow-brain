FROM amazonlinux:2023.2.20231026.0

ARG TIDB_VERSION=v7.5.0

VOLUME /tidb-data

RUN rpm --import https://repo.mysql.com/RPM-GPG-KEY-mysql-2022
RUN dnf -y localinstall https://dev.mysql.com/get/mysql80-community-release-el9-5.noarch.rpm

RUN dnf -y update && dnf -y install glibc-langpack-ja procps-ng psmisc tar gzip mysql-community-client
RUN curl --proto '=https' --tlsv1.2 -sSf https://tiup-mirrors.pingcap.com/install.sh | sh
RUN /root/.tiup/bin/tiup install pd:${TIDB_VERSION} tidb:${TIDB_VERSION} tiflash:${TIDB_VERSION} tikv:${TIDB_VERSION} prometheus:${TIDB_VERSION} \
  && mkdir -p /root/.tiup/data \
  && ln -sf /tidb-data /root/.tiup/data/devenv

ENV PATH=/root/.tiup/bin:$PATH \
    LANG="ja_JP UTF8" \
    LANGUAGE="ja_JP:ja" \
    LC_ALL="ja_JP.UTF8" \
    TZ="UTC"

EXPOSE 2379 3000 4000 9090 10080
