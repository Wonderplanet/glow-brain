<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // s3バケット マスタデータ
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        // s3バケット バナー
        's3_banner' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BANNER_BUCKET'),
            'url' => env('AWS_BANNER_BUCKET_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'cloudfront_distribution_id' => env('AWS_BANNER_CLOUDFRONT_DISTRIBUTION_ID'),
        ],

        // s3バケット information
        's3_information' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_INFORMATION_BUCKET'),
            'url' => env('AWS_INFORMATION_BUCKET_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'cloudfront_distribution_id' => env('AWS_INFORMATION_CLOUDFRONT_DISTRIBUTION_ID'),
        ],

        // s3バケット s3_webview
        's3_webview' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_WEBVIEW_BUCKET'),
            'url' => env('AWS_WEBVIEW_BUCKET_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'cloudfront_distribution_id' => env('AWS_WEBVIEW_CLOUDFRONT_DISTRIBUTION_ID'),
        ],

        // ガシャ事後検証用jsonのアップロード先S3
        's3_bne' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID_BNE'),
            'secret' => env('AWS_SECRET_ACCESS_KEY_BNE'),
            'region' => env('AWS_DEFAULT_REGION_BNE'),
            'bucket' => env('AWS_BUCKET_BNE'),
            'url' => env('AWS_URL_BNE'),
            'endpoint' => env('AWS_ENDPOINT_BNE'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT_BNE', false),
            'throw' => false,
        ],

        // s3バケット glow-develop-asset
        's3_asset' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_ASSET_BUCKET'),
            'url' => env('AWS_ASSET_BUCKET_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        // データ昇格時のコピー元環境のバケット情報
        's3_source' => [
            'driver' => 's3',
            'region' => env('AWS_DEFAULT_REGION'),
            'role_arn' => env('AWS_SOURCE_ROLE_ARN'),
            'local' => [ // デバッグ用
                'banner_bucket' => 'glow-develop-banner',
                'webview_bucket' => 'glow-develop-webview',
                'information_bucket' => 'glow-develop-information',
                'admin_bucket' => 'glow-develop-admin',
            ],
            'develop' => [
                'banner_bucket' => 'glow-develop-banner',
                'webview_bucket' => 'glow-develop-webview',
                'information_bucket' => 'glow-develop-information',
                'admin_bucket' => 'glow-develop-admin',
            ],
            'dev_ld' => [
                'banner_bucket' => 'glow-dev-ld-banner',
                'webview_bucket' => 'glow-dev-ld-webview',
                'information_bucket' => 'glow-dev-ld-information',
                'admin_bucket' => 'glow-dev-ld-admin',
            ],
            'dev_qa' => [
                'banner_bucket' => 'glow-dev-qa-banner',
                'webview_bucket' => 'glow-dev-qa-webview',
                'information_bucket' => 'glow-dev-qa-information',
                'admin_bucket' => 'glow-dev-qa-admin',
            ],
            'dev_qa2' => [
                'banner_bucket' => 'glow-dev-qa2-banner',
                'webview_bucket' => 'glow-dev-qa2-webview',
                'information_bucket' => 'glow-dev-qa2-information',
                'admin_bucket' => 'glow-dev-qa2-admin',
            ],
            'qa' => [
                'banner_bucket' => 'glow-qa-banner',
                'webview_bucket' => 'glow-qa-webview',
                'information_bucket' => 'glow-qa-information',
                'admin_bucket' => 'glow-qa-admin',
            ],
            'staging' => [
                'banner_bucket' => 'glow-staging-banner',
                'webview_bucket' => 'glow-staging-webview',
                'information_bucket' => 'glow-staging-information',
                'admin_bucket' => 'glow-staging-admin',
            ],
            'review' => [
                'banner_bucket' => 'glow-review-banner',
                'webview_bucket' => 'glow-review-webview',
                'information_bucket' => 'glow-review-information',
                'admin_bucket' => 'glow-review-admin',
            ],
            'production' => [
                'banner_bucket' => 'glow-prod-banner',
                'webview_bucket' => 'glow-prod-webview',
                'information_bucket' => 'glow-prod-information',
                'admin_bucket' => 'glow-prod-admin',
            ],
        ],

        // BankKPI用
        'bank_kpi_temp' => [
            'driver' => 'local',
            'root' => storage_path('app/bank_kpi'),
            'throw' => false,
        ],
        'bank_kpi_f001' => [
            'driver' => 'local',
            'root' => storage_path('app/bank_kpi/f001'),
            'throw' => false,
        ],
        'bank_kpi_f002' => [
            'driver' => 'local',
            'root' => storage_path('app/bank_kpi/f002'),
            'throw' => false,
        ],
        'bank_kpi_f003_daily' => [
            'driver' => 'local',
            'root' => storage_path('app/bank_kpi/f003_daily'),
            'throw' => false,
        ],
        'bank_kpi_f003_monthly' => [
            'driver' => 'local',
            'root' => storage_path('app/bank_kpi/f003_monthly'),
            'throw' => false,
        ],
        'bank_kpi_s3' => [
            'driver' => 's3',
            'key' => env('BANK_AWS_ACCESS_KEY_ID'),
            'secret' => env('BANK_AWS_SECRET_ACCESS_KEY'),
            'region' => env('BANK_AWS_DEFAULT_REGION'),
            'bucket' => env('BANK_AWS_BUCKET'),
            'use_path_style_endpoint' => false,
            'throw' => false,
        ],

        // Datalake用
        'datalake_temp' => [
            'driver' => 'local',
            'root' => storage_path('app/datalake'),
            'throw' => false,
        ],
        'datalake_gcs' => [
            'driver' => 'gcs',
            'project_id' => env('DATALAKE_GCS_PROJECT_ID'),
            'key_file' => (function() {
                // DATALAKE_GCS_KEY（JSON文字列）が指定されている場合
                if (env('DATALAKE_GCS_KEY')) {
                    return json_decode(env('DATALAKE_GCS_KEY'), true);
                }
                // DATALAKE_GCS_KEY_FILE（ファイルパス）が指定されている場合
                if (env('DATALAKE_GCS_KEY_FILE')) {
                    $filePath = storage_path(env('DATALAKE_GCS_KEY_FILE'));
                    if (file_exists($filePath)) {
                        return json_decode(file_get_contents($filePath), true);
                    }
                }
                return null;
            })(),
            'bucket' => env('DATALAKE_GCS_BUCKET'),
            'path_prefix' => null,
            'visibility' => 'noPredefinedVisibility',
            'throw' => true
        ],

        /**
         * アセットを管理しているS3の接続情報(各環境分)
         */
        's3_asset_local' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-develop-asset',
            'throw' => false,
        ],
        's3_asset_develop' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-develop-asset',
            'throw' => false,
        ],
        's3_asset_dev_ld' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-dev-ld-asset',
            'throw' => false,
        ],
        's3_asset_dev_qa' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-dev-qa-asset',
            'throw' => false,
        ],
        's3_asset_dev_qa2' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-dev-qa2-asset',
            'throw' => false,
        ],
        's3_asset_qa' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-qa-asset',
            'throw' => false,
        ],
        's3_asset_staging' => [
            'driver' => 's3',
            'key' => env('AWS_STAGING_ACCESS_KEY_ID'),
            'secret' => env('AWS_STAGING_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-staging-asset',
            'throw' => false,
        ],
        's3_asset_review' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-review-asset',
            'throw' => false,
        ],
        's3_asset_production' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-prod-asset',
            'throw' => false,
        ],

        /**
         * マスターデータdumpファイルを管理しているS3の接続情報(各環境分)
         */
        's3_master_dump_local' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-develop-admin',
            'throw' => false,
        ],
        's3_master_dump_develop' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-develop-admin',
            'throw' => false,
        ],
        's3_master_dump_dev_ld' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-dev-ld-admin',
            'throw' => false,
        ],
        's3_master_dump_dev_qa' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-dev-qa-admin',
            'throw' => false,
        ],
        's3_master_dump_dev_qa2' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-dev-qa2-admin',
            'throw' => false,
        ],
        's3_master_dump_qa' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-qa-admin',
            'throw' => false,
        ],
        's3_master_dump_staging' => [
            'driver' => 's3',
            'key' => env('AWS_STAGING_ACCESS_KEY_ID'),
            'secret' => env('AWS_STAGING_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-staging-admin',
            'throw' => false,
        ],
        's3_master_dump_review' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-review-admin',
            'throw' => false,
        ],
        's3_master_dump_prod' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-prod-admin',
            'throw' => false,
        ],

        /**
         * クライアント参照用マスターデータファイルを管理しているS3の接続情報(各環境分)
         */
        's3_client_master_data_local' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-develop-master',
            'throw' => false,
        ],
        's3_client_master_data_develop' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-develop-master',
            'throw' => false,
        ],
        's3_client_master_data_dev_ld' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-dev-ld-master',
            'throw' => false,
        ],
        's3_client_master_data_dev_qa' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-dev-qa-master',
            'throw' => false,
        ],
        's3_client_master_data_dev_qa2' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-dev-qa2-master',
            'throw' => false,
        ],
        's3_client_master_data_qa' => [
            'driver' => 's3',
            'key' => env('AWS_ALL_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ALL_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-qa-master',
            'throw' => false,
        ],
        's3_client_master_data_staging' => [
            'driver' => 's3',
            'key' => env('AWS_STAGING_ACCESS_KEY_ID'),
            'secret' => env('AWS_STAGING_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => 'glow-staging-master',
            'throw' => false,
        ],

        's3_env_file' => [
            'driver' => 's3',
            'key' => env('AWS_ENV_S3_ACCESS_KEY_ID'),
            'secret' => env('AWS_ENV_S3_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_ENV_S3_BUCKET', 'glow-shared-dev-envs'),
            'cloudfront_distribution_id' => env('AWS_ENV_CLOUDFRONT_DISTRIBUTION_ID'),
            'throw' => false,
        ],

        // WPデータレイク用S3バケット
        's3_wp_datalake' => [
            'driver' => 's3',
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_WP_DATALAKE_BUCKET'),
            'throw' => false,
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
        public_path('asset/glow_client_asset') => storage_path('app/glow_client/Assets/GLOW'),
    ],

];
