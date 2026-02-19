<?php

namespace App\Constants;

class DatalakeConstant
{
    // 一回のレコード処理件数
    public const RECORD_FETCH_LIMIT = 5000;

    // 一時保存用ファイル置き場
    public const DISK_TEMP = 'datalake_temp';

    // GCS
    public const DISK_GCS = 'datalake_gcs';

    public const FILE_NAME_FORMAT = '%s-%s.part%012d.json';

    public const FILE_NAME_COLUMN_FORMAT = 'dl_%s_%s-%s.part%012d.json';

    // {DB種別}__{DB名}-{転送方式}-{環境種別}/{yyyy}/{mm}/{dd}/{テーブル名}/圧縮ファイル名
    public const GCS_FILE_UPLOAD_PATH = '%s/%s/%s/%s/%s/%s';

    // 1GBまでのファイルまでしか転送不可の為、995MBまでで抑える
    public const FILE_SIZE_LIMIT = 995 * 1024 * 1024;

    public const GCS_PREFIX_MST = 'mysql__%s-full-%s';
    public const GCS_PREFIX_OPR = 'mysql__%s-full-%s';
    public const GCS_PREFIX_USR = 'tidb__%s-full-%s';
    public const GCS_PREFIX_LOG = 'tidb__%s-diff-%s';

    // [yyyy/mm/dd] データレイク[実行種別] [メッセージ]
    public const SLACK_MESSAGE_BASE = '%s データレイク%s %s';
    public const SLACK_MESSAGE_UNKNOWN = 'ログが見つかりません。';
    public const SLACK_MESSAGE_RUNNING = '実行中です。管理ツールを確認してください。';
    public const SLACK_MESSAGE_ALERT = '管理ツールでステータスを確認し、再送の手動実行を行なってください。';
    public const SLACK_MESSAGE_URL = '%s/admin/datalake-transfers';

    /**
     * WP Datalake S3 へアップロードしないテーブルリスト
     * @var array<string>
     */
    public const WP_DATALAKE_S3_UPLOAD_BLACKLIST_TABLES = [
        'log_user_profiles',
        'log_pvp_actions',
    ];

    /**
     * WP Datalake S3 へアップロードする対象テーブルリスト
     * 今後usr系テーブルを追加する場合はここに追記する
     * @var array<string>
     */
    public const WP_DATALAKE_S3_UPLOAD_TARGET_TABLES = [
        'usr_users',
    ];

    /**
     * WP Datalake S3 メタデータ領域のパス
     */
    public const WP_DATALAKE_S3_METADATA_SCHEMA_PREFIX = 'metadata/schema/';
}
