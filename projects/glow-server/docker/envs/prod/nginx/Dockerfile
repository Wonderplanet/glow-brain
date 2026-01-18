FROM amazonlinux:2023.1.20230719.0

RUN dnf install -y nginx

COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
RUN mkdir -p /var/www/api
COPY ./api /var/www/api
RUN chmod -R 777 /var/www/api/storage
RUN chmod -R 777 /var/www/api/public

CMD ["nginx", "-g", "daemon off;"]