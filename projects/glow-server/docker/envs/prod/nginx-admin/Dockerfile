FROM amazonlinux:2023.1.20230719.0

RUN dnf install -y nginx

COPY ./docker/nginx-admin/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/nginx-admin/nginx.conf /etc/nginx/nginx.conf
RUN mkdir -p /var/www/admin
COPY ./admin /var/www/admin
RUN chmod -R 777 /var/www/admin/storage
RUN chmod -R 777 /var/www/admin/public

CMD ["nginx", "-g", "daemon off;"]
