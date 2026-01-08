#!/bin/bash

cd $(dirname $0)

# PHPコンテナが起動してるかチェック
./bin/sail-wp php -v 1> /dev/null
if [ $? != 0 ]; then
    # sail-wp側のエラーメッセージが表示されてるはずなので、何もせず終了
    exit 1
fi

echo '===== コーディング規約のチェックの実行 ====='

# 実行されるコマンド:
#   docker-compose exec ${SERVICE_NAME} vendor/bin/phpcbf
./bin/sail-wp phpcbf
./bin/sail-wp phpcs

echo '===== 静的解析の実行 ====='

# 実行されるコマンド:
#   docker-compose exec ${SERVICE_NAME} vendor/bin/phpstan analyse --memory-limit=-1
./bin/sail-wp phpstan

echo '===== アーキテクチャテストの実行 ====='

# 実行されるコマンド:
#   docker-compose exec ${SERVICE_NAME} vendor/bin/deptrac analyse
./bin/sail-wp deptrac

echo '===== テストの実行 ====='

# 実行されるコマンド:
#   docker-compose exec ${SERVICE_NAME} php artisan test --coverage | grep -v '100.0 %'
./bin/sail-wp test --coverage | grep -v '100.0 %'

# echo '===== 管理画面テストの実行 ====='

# 実行されるコマンド:
#   docker-compose exec php-admin php artisan test --coverage | grep -v '100.0 %'
# ./bin/sail-wp admin test --coverage | grep -v '100.0 %'
