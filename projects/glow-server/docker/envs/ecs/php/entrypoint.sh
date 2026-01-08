#!/bin/sh

php artisan config:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache

# メインのエントリーポイントを実行（例: PHP-FPM or Laravel サーバー）
exec "$@"
