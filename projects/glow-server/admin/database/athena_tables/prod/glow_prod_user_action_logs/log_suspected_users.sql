-- log_suspected_users
CREATE EXTERNAL TABLE `glow_prod_user_action_logs`.`log_suspected_users` (
    `id` string COMMENT 'UUID',
    `usr_user_id` string COMMENT 'usr_users.id',
    `nginx_request_id` string COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
    `request_id` string COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
    `logging_no` int COMMENT 'APIリクエスト中でのログの順番',
    `content_type` string COMMENT 'コンテンツのタイプ',
    `target_id` string COMMENT '降臨バトルの場合はmst_advent_battles.id',
    `cheat_type` string COMMENT 'mst_cheat_settings.cheat_type',
    `detail` string COMMENT 'チート判定要因のデータ',
    `suspected_at` string COMMENT 'チート疑いされた日時',
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
  's3://glow-prod-datalake/raw/tidb/log_suspected_users'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-prod-datalake/raw/tidb/log_suspected_users/${dt}/'
);
