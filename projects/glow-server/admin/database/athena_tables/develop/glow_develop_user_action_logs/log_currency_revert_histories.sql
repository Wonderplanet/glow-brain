-- log_currency_revert_histories
CREATE EXTERNAL TABLE `glow_develop_user_action_logs`.`log_currency_revert_histories` (
    `id` string COMMENT 'UUID',
    `usr_user_id` string COMMENT 'ユーザーID',
    `comment` string COMMENT 'コメント',
    `log_trigger_type` string COMMENT '対象ログのトリガータイプ',
    `log_trigger_id` string COMMENT '対象ログのトリガーID',
    `log_trigger_name` string COMMENT '対象ログのトリガー名',
    `log_trigger_detail` string COMMENT '対象ログのそのほかの付与情報',
    `log_request_id_type` string COMMENT '対象ログのリクエスト識別IDの種類',
    `log_request_id` string COMMENT '対象ログのリクエストID',
    `log_created_at` string COMMENT '対象ログの作成日時',
    `request_id_type` string COMMENT 'リクエスト識別IDの種類',
    `log_change_paid_amount` bigint COMMENT '変更対象の有償通貨',
    `log_change_free_amount` bigint COMMENT '変更対象の無償通貨',
    `trigger_type` string COMMENT 'トリガータイプ',
    `trigger_id` string COMMENT 'トリガーID',
    `trigger_name` string COMMENT 'トリガー名',
    `trigger_detail` string COMMENT 'トリガー詳細',
    `request_id` string COMMENT 'リクエスト識別ID',
    `nginx_request_id` string COMMENT 'nginxのリクエスト識別ID',
    `created_at` string COMMENT '作成日時のタイムスタンプ',
    `updated_at` string COMMENT '更新日時のタイムスタンプ'
)
PARTITIONED BY (
    `dt` string
)
ROW FORMAT SERDE 'org.apache.hadoop.hive.serde2.OpenCSVSerde'
  WITH SERDEPROPERTIES (
    "separatorChar" = ",",
    'quoteChar' = '"',
    "serialization.null.format" = "\\N"
  )
STORED AS INPUTFORMAT
  'org.apache.hadoop.mapred.TextInputFormat'
OUTPUTFORMAT
  'org.apache.hadoop.hive.ql.io.HiveIgnoreKeyTextOutputFormat'
LOCATION
  's3://glow-develop-datalake/raw/tidb/log_currency_revert_histories'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-develop-datalake/raw/tidb/log_currency_revert_histories/${dt}/'
);
