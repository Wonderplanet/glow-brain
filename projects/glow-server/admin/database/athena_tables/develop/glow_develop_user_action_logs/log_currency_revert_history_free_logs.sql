-- log_currency_revert_history_free_logs
CREATE EXTERNAL TABLE `glow_develop_user_action_logs`.`log_currency_revert_history_free_logs` (
    `id` string COMMENT 'UUID',
    `usr_user_id` string COMMENT 'ユーザーID',
    `log_currency_revert_history_id` string COMMENT 'log_currency_revert_historiessのID',
    `log_currency_free_id` string COMMENT '実行した際のlog_currency_freesのID',
    `revert_log_currency_free_id` string COMMENT 'log_currency_freesの返却対象としたログのID',
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
  's3://glow-develop-datalake/raw/tidb/log_currency_revert_history_free_logs'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-develop-datalake/raw/tidb/log_currency_revert_history_free_logs/${dt}/'
);
