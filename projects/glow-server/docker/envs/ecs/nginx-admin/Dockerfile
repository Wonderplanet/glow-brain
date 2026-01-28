FROM amazonlinux:2023.2.20231026.0

RUN dnf install -y nginx

COPY ./docker/envs/ecs/nginx-admin/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/envs/ecs/nginx-admin/nginx.conf /etc/nginx/nginx.conf
RUN mkdir -p /var/www/admin
COPY ./admin /var/www/admin
RUN chmod -R 777 /var/www/admin/storage
RUN chmod -R 777 /var/www/admin/public

# 管理ツール表示用アセットの表示のための設定
RUN mkdir -p /var/www/admin/public/asset
RUN ln -s /var/www/admin/storage/app/glow_client/Assets/GLOW /var/www/admin/public/asset/glow_client_asset

CMD ["nginx", "-g", "daemon off;"]
