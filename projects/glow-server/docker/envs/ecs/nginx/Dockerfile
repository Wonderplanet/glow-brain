FROM amazonlinux:2023.2.20231026.0

RUN dnf install -y nginx

COPY ./docker/envs/ecs/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/envs/ecs/nginx/nginx.conf /etc/nginx/nginx.conf
RUN mkdir -p /var/www/api
COPY ./api /var/www/api
RUN chmod -R 777 /var/www/api/storage
RUN chmod -R 777 /var/www/api/public

CMD ["nginx", "-g", "daemon off;"]
