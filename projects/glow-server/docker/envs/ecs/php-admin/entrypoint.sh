#!/bin/bash

# 管理ツール表示用アセットの表示のための設定
chown -R nginx:nginx /var/www/admin/storage/app/glow_client/

# nginxユーザーでapp:fetch-admin-assetsコマンドを初回起動時にバックグラウンドで実行
# 実行完了後はプロセスが終了する
su -s /bin/bash nginx -c "cd /var/www/admin && php artisan app:fetch-admin-assets" &

# nginxユーザーでLaravel スケジューラーをバックグラウンドで起動
# rootでの実行を避けて権限問題を回避
su -s /bin/bash nginx -c "cd /var/www/admin && php artisan schedule:work" &

# PHP-FPMをフォアグラウンドで起動（メインプロセス）
exec /usr/sbin/php-fpm --nodaemonize
