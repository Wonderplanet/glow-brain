#!/bin/bash

# エラーが出たら即時終了
set -e

# このスクリプトが存在するディレクトリに移動
cd $(dirname $0)

# .envをコピー
cp ../api/.env.example ../api/.env
cp ../admin/.env.example ../admin/.env

# コンテナを作成して起動
# 実行されるコマンド:
#   docker-compose up -d --build
./bin/sail-wp up -d --build

# PHPのパッケージをインストール
# 実行されるコマンド:
#   docker-compose exec ${SERVICE_NAME} composer install
./bin/sail-wp composer install
./bin/sail-wp admin composer install

# パッケージの設定ファイルなどをインストール
./bin/sail-wp artisan vendor:publish --tag=wp
./bin/sail-wp admin artisan vendor:publish --tag=wp-admin

# DBの生成
# 実行されるコマンド:
#   docker-compose exec ${SERVICE_NAME} php artisan db:create ${CONNECTION} ${DATABASE}
./bin/sail-wp artisan db:create mst local # mstコネクションを使ってlocalという名前のDBを作成(マスタDBをlocalという命名で作成)
./bin/sail-wp artisan db:create mst local_test
./bin/sail-wp artisan db:create mng mng
./bin/sail-wp artisan db:create mng mng_test
./bin/sail-wp artisan db:create tidb local
./bin/sail-wp artisan db:create tidb local_test
./bin/sail-wp artisan db:create admin admin
./bin/sail-wp artisan db:create admin admin_test

# DBのマイグレーションを実行
# 実行されるコマンド:
#   docker-compose exec ${SERVICE_NAME} php artisan migrate"
./bin/sail-wp artisan migrate
./bin/sail-wp artisan migrate --env=local_test
./bin/sail-wp admin artisan migrate
./bin/sail-wp admin artisan migrate --env=admin_test

# 初期データ入れるためにseedを実行
./bin/sail-wp artisan db:seed
./bin/sail-wp admin artisan db:seed

./bin/sail-wp admin npm install
./bin/sail-wp admin npm run build

# 管理ツール上でアセットを参照するためのシンボリックリンクを作成（admin/config/filesystems.php に設定）
mkdir -p ../admin/public/asset
./bin/sail-wp admin artisan storage:link
