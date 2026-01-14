FROM amazonlinux:2023.2.20231026.0

RUN dnf install -y nginx

COPY ./default.conf /etc/nginx/conf.d/default.conf
COPY ./nginx.conf /etc/nginx/nginx.conf

CMD ["nginx", "-g", "daemon off;"]
