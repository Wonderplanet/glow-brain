<?php
// TODO: v0.3.0ではGit上のCSVからの読み取りのみ対応
// 環境デプロイ時は環境変数から受け取る
$json = env('SHEET_API_CREDENTIALS');
if (!empty($json)) {
    // 環境変数から受け取ったらファイルとして保存
    $googleKeyPath = base_path('storage/app/env/glow-spread-sheet-access.json');
    if (!is_dir(base_path('storage/app/env'))) {
        mkdir(base_path('storage/app/env'), 0777, true);
    }
    file_put_contents($googleKeyPath, $json);
} else {
    // 環境変数にないローカルか設定漏れ
    $googleKeyPath = base_path('storage/app/env/glow-spread-sheet-access.json');
}

/**
 * アセット・マスターインポートツール関連の設定
 */
return [
    /**
     * 取り込みが可能な環境一覧
     * PJに合わせてenvを設定してください
     */
    'importable_from_environment_list' => explode(',', env('IMPORTABLE_FROM_ENVIRONMENT_LIST')),

    /**
     * 各環境のドメイン設定
     * PJに合わせて環境名と管理ツールのドメインを設定してください
     */
    'admin_api_domain' => [
        'develop' => 'https://develop-admin.glow.nappers.jp',
        'dev_ld' => 'https://admin.dev-ld.glow.nappers.jp',
        'dev_qa' => 'https://admin.dev-qa.glow.nappers.jp',
        'dev_qa2' => 'https://admin.dev-qa2.glow.nappers.jp',
        'qa' => 'https://admin.qa.glow.nappers.jp',
        'staging' => 'https://admin.staging.glow.nappers.jp'
    ],

    /**
     * アセット配信管理画面に表示するJenkinsのURL
     * PJに合わせてenvを設定してください
     */
    'jenkins_url_list' => [
        'iOS' => env('IOS_ASSET_CREATE_JENKINS_URL'),
        'Android' => env('ANDROID_ASSET_CREATE_JENKINS_URL'),
    ],

    /**
     * マスターデータ環境間インポート用mysqldumpファイル配置s3バケット
     * PJに合わせてenvを設定してください
     */
    'master_data_mysqldump_bucket' => env('AWS_MASTER_DATA_MYSQLDUMP_BUCKET'),

    /**
     * マスターデータ環境間インポート用 mysqldumpファイルの接頭辞リスト
     * s3からファイルを取得する際に使用する
     * キーは.env.IMPORTABLE_FROM_ENVIRONMENT_LISTで指定している環境名
     * PJに合わせてキーと値を設定してください
     */
    'master_data_mysqldump_file_prefix' => [
        'develop' => 'develop',
        'dev_ld' => 'dev_ld',
        'dev_qa' => 'dev_qa',
        'dev_qa2' => 'dev_qa2',
        'qa' => 'qa',
        'staging' => 'staging',
    ],

    /**
     * gitリポジトリ設定
     * 環境に合わせてenvを設定してください
     */
    'repositoryUrl' => env('GIT_REPOSITORY'),

    /**
     * マスターデータのgitブランチ設定
     * 環境に合わせてenvを設定してください
     */
    'gitBranch' => env('GIT_BRANCH'),

    /**
     * google drive 設定
     * PJに合わせてenvなどを設定してください
     * TODO: seed-v0.3.0ではGit上のCSVからの読み取りのみ対応
     */
    'googleCredentialPath' => $googleKeyPath,
    'googleSpreadSheetDirId' => env('SHEET_DIR_ID'),
    'googleSpreadSheetNamePrefix' => env('SHEET_NAME_PREFIX'),
    'googleSpreadSheetOprNamePrefix' => env('SHEET_NAME_OPR_PREFIX'),
    'serializeDataOutputSettingSheetName' => env('OUTPUT_SHEET_NAME'),
    'validationSettingSheetNames' => [
        'ReferenceCheckSetting',
        'DuplicateCheckSetting',
    ],

    /**==== storageなどのファイル設定 PJに合わせて設定してください ====**/

    /**
     * Git管理下にあるCSVファイルのディレクトリ
     */
    'spreadSheetCsvDir' => base_path('storage/app/masterdata_csv'),

    /**
     * データベースに取り込める形式のCSVファイルのディレクトリ
     */
    'databaseCsvDir' => base_path('storage/app/database_csv'),

    /**
     * クライアント用にシリアライズしたJSONファイルのディレクトリ
     */
    'serializedFileDir' => base_path('storage/app/client_json'),

    /**
     * バリデーション用のCSVファイルのディレクトリ
     */
    'validationCsvDir' => base_path('storage/app/masterdata_csv/masterdata_validation/csv'),
    'validationDirName' => 'masterdata_validation',

    /**
     * リリースバージョンDBで実行するマイグレーションディレクトリ
     */
    'migrationFileDir' => 'vendor/wonder-planet/app-share/database/migrations/mst',

    /**
     * 環境間インポート用に生成した対象リリースバージョンDBのdumpファイル用ディレクトリ
     */
    'masterDataMysqlDump' => base_path('storage/app/masterdata_mysqldump'),

    /**
     * 環境間インポート用に生成した対象リリースバージョンDBのdumpファイル用ディレクトリ
     */
    'downloadMasterDataMysqlDump' => 'download_masterdata_mysqldump',

    /**
     * マスターモデルのパス
     */
    'masterResourceModelsPath' => [
        'mst' => "\App\Domain\Resource\Mst\Models\\",
        'opr' => "\App\Domain\Resource\Mst\Models\\",
    ],

    /**
     * messagePackとJSONの作成時の対象となるnamespace
     */
    'clientMasterdataModelNameSpace' => [
        'masterData' => "App\Http\Resources\Api\Masterdata",
        'operationdata' => "App\Http\Resources\Api\Operationdata",
        'masteri18ndata' => "App\Http\Resources\Api\Masteri18ndata",
        'operationi18ndata' => "App\Http\Resources\Api\Operationi18ndata",
    ],

    /**
     * マスターデータインポート系のタイムアウト時間を延長
     */
    'master_data_import_timeout_seconds' => 900,
];
