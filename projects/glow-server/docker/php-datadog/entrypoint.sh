#!/bin/bash
# 環境構築時&環境立ち上げ時に実行するスクリプト
# api/storage以下のディレクトリの所有者をnginxユーザーに変更
find storage -type d -exec chown nginx {} \;
find storage -type d -exec chgrp nginx {} \;

# 環境構築時にlaravel.logがrootユーザーで作成されてしまいnginxユーザーが書き込めなくなってしまうので
# 回避の為所有者をrootユーザーからnginxユーザーに変更している
touch storage/logs/laravel.log
chown nginx storage/logs/laravel.log
chgrp nginx storage/logs/laravel.log

# メインプロセスを起動
exec "$@"
