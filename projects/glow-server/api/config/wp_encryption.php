<?php

return [
    // 本番環境の環境名（app.env）
    'production_env_name' => env('PRODUCTION_ENV_NAME', 'production'),

    // 全体の暗号化処理自体を有効化するかどうか
    'enabled' => env('ENCRYPTION_ENABLED', false),

    // 暗号化パスワード
    'request_password' => env('ENCRYPTION_REQUEST_PASSWORD', 'password'),
    'response_password' => env('ENCRYPTION_RESPONSE_PASSWORD', 'password'),

    // 暗号化処理を無効化するリクエストヘッダー名
    'disable_header' => env('DISABLE_HEADER', 'Disable-Encrypt-Api-Param'),

    // レスポンスに暗号化処理を施したかを示すフラグ（true：暗号化する）
    'response_encrypted_header' => env('RESPONSE_ENCRYPTED_HEADER', 'X-Response-Encrypted'),

    // 暗号化をする際にクライアントから渡されるsaltのヘッダー名
    'salt_header' => env('SALT_HEADER', 'Unique-Request-Identifier'),

    //////////////////////////////////////////
    // マスタデータ設定
    // マスターデータの暗号化を有効化するかどうか
    'master_data_enabled' => env('ENCRYPTION_MASTER_DATA_ENABLED', true),

    // マスタデータの暗号化パスワード
    // 役割は別なので、APIの暗号化パスワードとは別に設定する
    'master_data_password' => env('ENCRYPTION_MASTER_DATA_PASSWORD', 'master_data_password'),
];
