<?php

declare(strict_types=1);

/**
 * 通貨基盤で参照する設定ファイル
 *
 * 変更履歴などはlib/laravel-wp-currnecy 以下にある同名のファイルを参照すること
 */

return [
    /**
     * DB接続先情報
     *
     * 接続先のコネクションはプロダクトによって変更されるため、
     * ここで定義する
     *
     * キー値には、課金・通貨基盤で使用するコネクションを指定する。
     * 対応する値には、config/database.phpのconnectionsのキー値を指定する。
     *
     * TODO: 垂直分割に対応するためコネクションを分けて指定できるようにしてあるが、すべてデフォルトコネクション(mysql)を指定している。
     * laravelの処理migrate:freshやrefreshDatabaseやデフォルトのコネクションしか処理しないため、DBを別にすると失敗するので
     * 結果的に同じ場所に作っている…
     */
    'connections' => [
        // マスタデータを扱うコネクション
        'default_mst' => env('DB_CONNECTION_MST', 'default_mst'),
        'mst' => env('DB_CONNECTION_MST', 'mst'),
        // マネジデータを扱うコネクション
        'mng' => env('DB_CONNECTION_MNG', 'mng'),
        // ユーザーデータを扱うコネクション
        'usr' => env('DB_CONNECTION_USR', 'api'),
        // ログデータを扱うコネクション
        'log' => env('DB_CONNECTION_LOG', 'api'),
        // 管理ツールのデータを扱うコネクション
        'admin' => env('DB_CONNECTION_ADMIN', 'admin'),
    ],

    /**
     * テーブル名の設定
     *
     * テーブル名は各プロダクトごとに変更される可能性があるため、ここで定義する
     *
     * キー値には、課金・通貨基盤で使用するテーブル名を指定する。
     * このテーブル名を課金・通貨基盤ではデフォルト値とする。(ドキュメントなどでは、このデフォルトテーブル名を使用する)
     * 対応する値には、読み替える先のプロダクトごとのテーブル名を指定する。
     *
     * 削除済みテーブル (過去のマイグレーションファイルのために残しておく必要があるので記載している。消さないこと)
     * - log_currency_cashes
     */
    'tablenames' => [
        // mst
        'mst_store_products' => 'mst_store_products',
        // opr
        'opr_products' => 'opr_products',
        // usr
        'usr_currency_summaries' => 'usr_currency_summaries',
        'usr_currency_frees' => 'usr_currency_frees',
        'usr_currency_paids' => 'usr_currency_paids',
        'usr_store_infos' => 'usr_store_infos',
        'usr_store_allowances' => 'usr_store_allowances',
        'usr_store_product_histories' => 'usr_store_product_histories',
        // log
        'log_stores' => 'log_stores',
        'log_currency_paids' => 'log_currency_paids',
        'log_currency_frees' => 'log_currency_frees',
        'log_currency_cashes' => 'log_currency_cashes',
        'log_allowances' => 'log_allowances',
        'log_currency_revert_histories' => 'log_currency_revert_histories',
        'log_currency_revert_history_paid_logs' => 'log_currency_revert_history_paid_logs',
        'log_currency_revert_history_free_logs' => 'log_currency_revert_history_free_logs',
        'log_close_store_transactions' => 'log_close_store_transactions',
        // adm
        'adm_foreign_currency_rates' => 'adm_foreign_currency_rates',
        'adm_foreign_currency_daily_rates' => 'adm_foreign_currency_daily_rates',
        'adm_bulk_currency_revert_tasks' => 'adm_bulk_currency_revert_tasks',
        'adm_bulk_currency_revert_task_targets' => 'adm_bulk_currency_revert_task_targets',
        'adm_bulk_currency_revert_task_target_paid_logs' => 'adm_bulk_currency_revert_task_target_paid_logs',
        'adm_bulk_currency_revert_task_target_free_logs' => 'adm_bulk_currency_revert_task_target_free_logs',
        'adm_bulk_currency_revert_task_target_revert_history_logs' => 'adm_bulk_currency_revert_task_target_revert_history_logs',
    ],

    /**
     * 本番環境/開発環境の判定
     *
     * 開発環境の場合はtrueを返すメソッドを指定する。
     * ここに指定されているメソッドをConfig::get('is_debuggable_environment_function')() のように呼び出し、
     * trueであれば開発環境のみで動作する機能を実行する。
     * (たとえばレシートチェック時に、UnitTest用のレシートやUnityのFakeStoreレシートを通すなど)
     *
     * 例では無名の関数を指定しているが、メソッド名を指定しても良い。
     *
     * 本番環境か開発環境かの判定はプロダクトによって違うため、設定を通して判断する。
     *
     * メソッドの戻り値がfalseまたはnull、0である場合、またはメソッドが指定されていない場合は、開発環境専用の機能は実行されない。
     *
     * 例として.envのIS_DEBUGGABLE_ENVから値を取るようにしている。
     * (APP_DEBUGはスタックトレースの出力などの制御も兼ねているため、開発環境専用の機能の判定には使用しない)
     */
    'is_debuggable_environment_function' => function () {
        // config:cacheのときでも動作するように、configを経由する
        return config('wp_currency.is_debuggable_env');
    },
    // config:cacheに乗るように、configに含める
    'is_debuggable_env' => env('IS_DEBUGGABLE_ENV', false),

    /**
     * 集計ツールで、isSandboxチェック表示を制御するための設定
     * 本番環境でもsandboxデータを参照できるようにするため、isDebuggableEnvironmentとは別にしている
     */
    'enable_sandbox_aggregation' => env('ENABLE_SANDBOX_AGGREGATION', false),

    /**
     * 各ストアの設定
     *
     * ※ECSなどで環境変数に定義する場合は、.envでの設定を行わず、環境変数のみで設定すること
     *
     * 環境変数から読み込む場合、デフォルトで次のキーを使用します
     * - APPSTORE_BUNDLE_ID_PRODUCTION
     * - APPSTORE_BUNDLE_ID_SANDBOX
     * - GOOGLEPLAY_PACKAGE_NAME
     * - GOOGLEPLAY_PURCHASE_CREDENTIAL_ENV
     * - GOOGLEPLAY_PUBKEY_ENV
     *
     * キー名などで変更を行いたい場合、定義されている環境変数名などを調整してください。
     */
    'store' => [
        // 有償/無償一次通貨の最大所有数
        // この値を超えると、通貨の付与が行われなくなる
        // 有償および無償の一次通貨の所持数合計で判定される
        //
        // 上限を無制限とする場合、-1を設定する
        'max_owned_currency_amount' => 999999999,

        // 無償通貨と有償通貨で上限チェックを分けるかどうか
        // true: 無償通貨と有償通貨で上限チェックを分ける
        // false: 無償通貨と有償通貨の所持数合計で上限チェックを行う
        'separate_currency_limit_check' => false,

        // 有償通貨の最大所有数
        'max_owned_paid_currency_amount' => 999999999,

        // 無償通貨の最大所有数
        'max_owned_free_currency_amount' => 999999999,

        'app_store' => [
            // プロダクション用のバンドルID
            'production_bundle_id' => env('APPSTORE_BUNDLE_ID_PRODUCTION', ''),
            // SandboxのバンドルID
            'sandbox_bundle_id' => env('APPSTORE_BUNDLE_ID_SANDBOX', ''),
            
            // StoreKit2設定
            'storekit2' => [
                // App Store Connect APIの外部トークン取得URL
                'external_token_url' => env('STOREKIT2_EXTERNAL_TOKEN_URL'),
                
                // JWT生成用設定（App Store Connect API認証用）
                'issuer' => env('STOREKIT2_ISSUER'),              // Issuer ID
                'key_id' => env('STOREKIT2_KEY_ID'),              // Key ID
                'private_key' => env('STOREKIT2_PRIVATE_KEY'),    // Private key content
                
                // Appleの公開鍵証明書保存ディレクトリ
                'cert_dir' => env('STOREKIT2_CERT_DIR'),

                // StoreKit API キャッシュ設定
                'api_cache' => [
                    // キャッシュの有効期限（分）
                    // デフォルト: 12時間（720分）
                    'ttl_minutes' => env('STOREKIT_API_CACHE_TTL_MINUTES', 720),
                ],
            ],
        ],
        'googleplay_store' => [
            // プロダクション/Sandboxのパッケージ名
            // GooglePlayの場合、開発ユーザーはストア側で判別するためパッケージ名は本番/開発環境で一緒になる
            'package_name' => env('GOOGLEPLAY_PACKAGE_NAME', ''),
            // 購入レシート確認用の証明書JSONファイルのパス
            'purchase_credential_file_path' => env('GOOGLEPLAY_PURCHASE_CREDENTIAL', ''),
            // 購入レシート確認用の証明書JSON文字列が格納された環境変数のキー
            // AWS Secret Managerなどで環境変数へ直接データを格納する場合に使用する
            //   ファイルの内容をずっとconfigやキャッシュ保持しないように、環境変数のキーのみを指定する
            'purchase_credential_env_key' => 'GOOGLEPLAY_PURCHASE_CREDENTIAL_ENV',
            // レシート検証用の公開鍵のパス
            'pubkey' => env('GOOGLEPLAY_PUBKEY', ''),
            // レシート検証用の公開鍵が格納された環境変数のキー
            // AWS Secret Managerなどで環境変数へ直接データを格納する場合に使用する
            //   ファイルの内容をずっとconfigやキャッシュに保持しないように、環境変数のキーのみを指定する
            'pubkey_env_key' => 'GOOGLEPLAY_PUBKEY_ENV',
        ],
    ],

    /**
     * ストアのテスト用レシートの設定
     *
     * ユニットテストでストア向けAPIの動作確認を行うためのレシートが保存されているパスを設定します。
     */
    'store_test' => [
        'app_store' => [
            'apple_sandbox_receipt' => '/var/local/wonderplanet/certificates/apple-sandbox-receipt.txt',
        ],
        'googleplay_store' => [
            'googleplay_sandbox_receipt' => '/var/local/wonderplanet/certificates/google-sandbox-receipt.txt',
        ],
    ],

    /**
     * 月末・月中平均の為替相場取得を行うかどうかの設定
     * USDなどが対象になる。
     * その他のリストはCurrencyAdminService::PARSE_CURRENCY_CODESを参照
     * 
     * デフォルトはtrue
     */
    'enable_scrape_foreign_rate' => env('ENABLE_SCRAPE_FOREIGN_RATE', true),

    /**
     * 現地参考為替相場の取得を行うかどうかの設定
     * TWDなどが対象になる。
     * その他のリストはCurrencyAdminService::PARSE_LOCAL_REFERENCE_CURRENCY_CODESを参照
     * 
     * デフォルトはfalse
     * ※取得先のファイルがエラーになる場合があるため、デフォルトはfalseにしている
     */
    'enable_scrape_local_reference' => env('ENABLE_SCRAPE_LOCAL_REFERENCE', false),

    ################################################################################
    # 廃止された設定
    ################################################################################
    /**
     * ログに記録されるリクエストIDの設定
     *
     * リクエストを識別するためクライアントで生成されたIDをヘッダから取得するキーを設定する
     * この設定はwp_common.request_unique_id_header_key に置き換えられたため、廃止する。
     * 
     * @see lib/laravel-wp-common/config/wp_common.php
     */
    // 'request_unique_id_header_key' => '',
];
