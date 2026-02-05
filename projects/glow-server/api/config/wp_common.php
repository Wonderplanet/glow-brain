<?php
/**
 * laravel-wp-frameworkライブラリにまたがる共通設定
 *
 * 使用するデータベースのコネクションなど、ライブラリにもまたがって共通で使用する設定を記載する
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
     **/
    'connections' => [
        // マスタデータを扱うコネクション
        'default_mst' => env('DB_CONNECTION_MST', 'default_mst'),
        'mst' => env('DB_CONNECTION_MST', 'mst'),
        // マネジデータを扱うコネクション
        'mng' => env('DB_CONNECTION_MNG', 'mng'),
        // ユーザーデータを扱うコネクション
        'usr' => env('DB_CONNECTION_USR', 'tidb'),
        // ログデータを扱うコネクション
        'log' => env('DB_CONNECTION_LOG', 'tidb'),
        // 管理ツールのデータを扱うコネクション
        'admin' => env('DB_CONNECTION_ADMIN', 'admin'),
    ],

    /**
     * リクエスト別のユニークIDを取得するためのヘッダーキー
     *
     * デフォルトはクライアントから送信される共通のリクエストIDヘッダーキー
     */
    'request_unique_id_header_key' => env('REQUEST_UNIQUE_ID_HEADER_KEY', 'Unique-Request-Identifier'),

    /**
     * フロントにあるシステムからのリクエストIDを取得するためのヘッダキー
     * front_request_idとして使用する。
     *
     * 次の順番で取得される
     * - nginxのrequest_id ($_SERVER['REQUEST_ID']で固定取得)
     * - request()->header(key) で取得できる値
     *
     * $keyは配列の先頭から順に取得し、最初に取得できたIDを採用する
     */
    'front_request_id_header_keys' => [
        'X-Amzn-Trace-Id', // ALBのX-Amzn-Trace-Id
    ],

    /**
     * データソースのタイムゾーン設定
     *
     * フロントの入力値をデータソースに保存する際のタイムゾーンを指定する
     * 例えばDBに保存する場合でDBのタイムゾーンがJSTなら `Asia/Tokyo` を指定する
     * デフォルトではUTC
     */
    'datasource_timezone' => 'UTC',

    // 特定パスで最新マスタデータを適用する設定
    // これらのパスでは、クライアントバージョンが取得できない場合に
    // 現在時刻時点で有効な最新のマスターリリースを適用する
    // バッチ処理やWebhookなど、クライアントバージョンが渡されないAPIで使用
    'use_latest_master_data_paths' => [
        'api/shop/webstore'
    ],
];
