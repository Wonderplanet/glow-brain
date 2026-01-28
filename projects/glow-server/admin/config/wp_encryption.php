<?php

return [
    // 本番環境の環境名（app.env）
    'production_env_name' => env('PRODUCTION_ENV_NAME', 'production'),

    // 全体の暗号化処理自体を有効化するかどうか
    'enabled' => env('ENCRYPTION_ENABLED', false),

    // 暗号化パスワード
    'password' => env('ENCRYPTION_PASSWORD', 'password'),

    // 暗号化処理を無効化するリクエストヘッダー名
    'disable_header' => env('DISABLE_HEADER', 'Disable-Encrypt-Api-Param'),

    // 暗号化をする際にクライアントから渡されるsaltのヘッダー名
    'salt_header' => env('SALT_HEADER', 'Unique-Request-Identifier'),

    //////////////////////////////////////////
    // マスタデータ設定
    // マスターデータの暗号化を有効化するかどうか
    'master_data_enabled' => env('ENCRYPTION_MASTER_DATA_ENABLED', false),

    // マスタデータの暗号化パスワード
    'master_data_password' => env('ENCRYPTION_MASTER_DATA_PASSWORD', 'master_data_password'),

    // 環境設定ファイルの暗号化パスワード
    'env_data_password' => env('ENCRYPTION_ENV_DATA_PASSWORD', 'password'),

    // 環境設定ファイルの暗号化パスワード
    'env_data_salt' => env('ENCRYPTION_ENV_DATA_SALT', 'salt'),
];
