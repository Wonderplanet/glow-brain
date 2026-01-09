<?php

// TODO: v0.3.0ではGit上のCSVからの読み取りのみ対応

// 環境デプロイ時は環境変数から受け取る
$json = env('SHEET_API_CREDENTIALS');
if (!empty($json)) {
    // 環境変数から受け取ったらファイルとして保存
    $google_key_path = base_path('storage/app/env/glow-spread-sheet-access.json');
    if (!is_dir(base_path('storage/app/env'))) {
        mkdir(base_path('storage/app/env'), 0777, true);
    }
    file_put_contents($google_key_path, $json);
} else {
    // 環境変数にないローカルか設定漏れ
    $google_key_path = base_path('storage/app/env/glow-spread-sheet-access.json');
}



return [
    // ログイン制限設定(maxAttempts回数失敗したらdecayMinutes分ロック)
    'loginLimit' => [
        'maxAttempts' => 5,
        'decayMinutes' => 10800,
    ],

    // git設定
    'repositoryUrl' => 'https://github.com/Wonderplanet/glow-masterdata.git',
    'clientRepositoryUrl' => 'https://github.com/Wonderplanet/glow-client.git',

    // google drive 設定
    // TODO: v0.3.0ではGit上のCSVからの読み取りのみ対応
    'googleCredentialPath' => $google_key_path,
    'googleSpreadSheetListSheetId' => env('SHEET_LIST_SHEET_ID', '1aURBLg7OAj142a2gqwjis7KV2D-ccEwgIdqQkC9dT54'),
    'googleSpreadSheetDirId' => env('SHEET_DIR_ID', '1oV8deaiDMRKGyJGGZneBAivXE04Gi6Bs'),
    'googleSpreadSheetNamePrefix' => env('SHEET_NAME_PREFIX', 'Mst'),
    'googleSpreadSheetOprNamePrefix' => env('SHEET_NAME_OPR_PREFIX', 'Opr'),
    'serializeDataOutputSettingSheetName' => env('OUTPUT_SHEET_NAME', 'マスター出力設定'),
    'validationSettingSheetNames' => [
        'ReferenceCheckSetting',
        'DuplicateCheckSetting',
    ],

    // ファイル設定
    'spreadSheetCsvDir' => base_path('storage/app/masterdata_csv'), // Git管理下にあるCSVファイルのディレクトリ
    'databaseCsvDir' => base_path('storage/app/database_csv'),      // データベースに取り込める形式のCSVファイルのディレクトリ
    'serializedFileDir' => base_path('storage/app/client_json'),    // クライアント用にシリアライズしたJSONファイルのディレクトリ
    'validationCsvDir' => base_path('storage/app/masterdata_csv/masterdata_validation/csv'), // バリデーション用のCSVファイルのディレクトリ
    'validationDirName' => 'masterdata_validation',
    'migrationFileDir' => 'vendor/wonder-planet/app-share/database/migrations',
    'informationDir' => base_path('storage/app/information'),
    'jumpPlusRewardDir' => base_path('storage/app/jump_plus_reward'),
    'envSerializedFileDir' => base_path('storage/app/env_setting'),
    'gachaCautionDir' => base_path('storage/app/gacha_caution'), // ガシャ注意事項のディレクトリ

    'glowClientDir' => base_path('storage/app/glow_client'), // glow_clientのディレクトリ
    'clientAssetDir' => base_path('storage/app/glow_client/Assets/GLOW/AssetBundles'), // Assetディレクトリ
    'clientBucketDir' => 'glow-client-asset/',

    'appId' => env('APP_ID', 'bana600'),
    'appName' => env('APP_NAME', 'glow_test_gacha'),
    'gachaSimulationReportMail' => '', //gasha_houkoku@bandainamcoent.co.jp宛に送る
    'gachaBucketDir' => 'glow-develop-gacha/',

    // データ昇格時のコピー元環境リスト
    'sourceEnvList' => [
        'local' => [],
        'develop' => [],
        'dev_ld' => [],
        'dev_qa' => ['dev_ld'],
        'dev_qa2' => ['dev_ld', 'develop'],
        'qa' => ['dev_ld'],
        'staging' => ['qa'],
        'review' => ['qa'],
        'production' => ['staging'],
    ],

    /**
     * 各環境のドメイン設定
     *
     * APP_ENVの値をキーにして、各環境のドメインを設定
     */
    'adminApiDomain' => [
        'local' => 'http://host.docker.internal:8081',
        'develop' => 'https://develop-admin.glow.nappers.jp',
        'dev_ld' => 'https://admin.dev-ld.glow.nappers.jp',
        'dev_qa' => 'https://admin.dev-qa.glow.nappers.jp',
        'dev_qa2' => 'https://admin.dev-qa2.glow.nappers.jp',
        'qa' => 'https://admin.qa.glow.nappers.jp',
        'staging' => 'https://admin.staging.glow.nappers.jp',
        'review' => 'https://admin.review.jumblerush.channel.or.jp',
        'production' => 'https://admin.jumblerush.channel.or.jp',
    ],

    'BneMailDomains' => [
        'bandainamcoent.co.jp',
    ],
];
