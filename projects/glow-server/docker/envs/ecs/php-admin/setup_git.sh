#!/bin/bash

GIT_USER=$1
GIT_TOKEN=$2
GIT_SSH_KEY=$3

if [ -z "$GIT_SSH_KEY" ]; then
    echo "GIT_SSH_KEY is not set. Using GIT_TOKEN for authentication."
    git config --global url."https://${GIT_USER}:${GIT_TOKEN}@github.com/".insteadOf "https://github.com/"
    ssh-keyscan github.com >> /etc/ssh/ssh_known_hosts
else
    echo "GIT_SSH_KEY is set. Using SSH for authentication."
    mkdir -p /etc/ssh
    echo -e "$GIT_SSH_KEY" > /etc/ssh/id_rsa

    cp /tmp/ssh_config /etc/ssh/ssh_config
    chown nginx:nginx /etc/ssh/id_rsa
    chmod 600 /etc/ssh/id_rsa
    chmod 644 /etc/ssh/ssh_config
    chmod 755 /etc/ssh
    ssh-keyscan github.com >> /etc/ssh/ssh_known_hosts

    git config --global url."git@github.com:".insteadOf "https://github.com/"
fi
