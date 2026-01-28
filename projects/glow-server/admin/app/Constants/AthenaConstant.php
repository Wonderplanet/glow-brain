<?php

namespace App\Constants;

class AthenaConstant
{
    // Athenaのクエリ実行状態
    public const ATHENA_QUERY_EXECUTION_STATE_QUEUED = 'QUEUED';
    public const ATHENA_QUERY_EXECUTION_STATE_RUNNING = 'RUNNING';
    public const ATHENA_QUERY_EXECUTION_STATE_SUCCEEDED = 'SUCCEEDED';
    public const ATHENA_QUERY_EXECUTION_STATE_FAILED = 'FAILED';
    public const ATHENA_QUERY_EXECUTION_STATE_CANCELLED = 'CANCELLED';

    // クエリ実行のポーリング設定
    // 最大待機時間（秒）。Athenaクエリを実行してからこの時間内に完了しない場合、タイムアウトと見なす
    public const ATHENA_QUERY_POLL_MAX_WAIT_TIME = 300;
    // ポーリング間隔（秒）
    public const ATHENA_QUERY_POLL_INTERVAL_SECONDS = 2;

    // クエリ結果の最大取得件数（1ページあたり）
    public const ATHENA_MAX_RESULTS_PER_PAGE = 1000;

    // クエリ結果の再利用の有効期限（分）
    public const ATHENA_QUERY_RESULT_REUSE_MAX_AGE_MINUTES = 30;

    // Athenaでクエリするデータの日時のタイムゾーン
    public const ATHENA_DATETIME_TIMEZONE = 'UTC';

    /**
     * 現在日時からこの日数より前のデータを取得する場合は DBではなく、Athenaクエリでデータ取得する
     *
     * TiDBのTTL設定により、ログDBのデータが削除されるまでの期間を考慮して設定してください。
     * 2025/09/18時点では、TTL=31日で設定しています
     */
    public const ATHENA_FALLBACK_BEFORE_DAYS = 30;

    /**
     * Athenaパーティション用の日付フォーマット
     *
     * Athena上でのパーティション設定に依存する
     */
    public const ATHENA_PARTITION_DATE_FORMAT = 'Y/m/d';

    /**
     * Athenaパーティション列名
     *
     * Athena上でのパーティション設定に依存する
     */
    public const ATHENA_PARTITION_COLUMN = 'dt';

    /**
     * Athenaクエリを実行可能な環境のリスト
     *
     * この環境以外ではAthenaクエリは実行されず、通常のDBクエリのみが使用される
     */
    public const ATHENA_ENABLED_ENVIRONMENTS = ['develop', 'production'];

    public const LOG_TABLE_FILTER_DATE_RANGE_LIMIT_DAYS = 10;

    /**
     * S3のデータソースパスフォーマット
     * {bucket}: S3バケット名、{table}: テーブル名
     */
    public const S3_DATA_SOURCE_PATH_FORMAT = 's3://{bucket}/raw/tidb/{table}';

    /**
     * S3のパーティションパスフォーマット
     * {bucket}: S3バケット名、{table}: テーブル名
     */
    public const S3_PARTITION_PATH_FORMAT = 's3://{bucket}/raw/tidb/{table}/${dt}/';
}
