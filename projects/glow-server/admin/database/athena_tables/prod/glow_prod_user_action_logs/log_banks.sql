-- log_banks
CREATE EXTERNAL TABLE `glow_prod_user_action_logs`.`log_banks` (
    `id` string COMMENT '',
    `usr_user_id` string COMMENT 'usr_users.id',
    `nginx_request_id` string COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
    `request_id` string COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
    `logging_no` int COMMENT 'APIリクエスト中でのログの順番',
    `event_id` string COMMENT 'イベントID',
    `platform_user_id` string COMMENT 'プラットフォーム別識別番号',
    `user_first_created_at` string COMMENT 'ユーザー初回登録日時',
    `user_agent` string COMMENT 'ユーザーエージェント',
    `os_platform` int COMMENT 'OSプラットフォーム。UserConstantのPLATFORM_XXXの値。',
    `os_version` string COMMENT 'OSバージョン',
    `country_code` string COMMENT '国コード',
    `ad_id` string COMMENT '広告ID',
    `request_at` string COMMENT 'APIリクエスト日時',
    `created_at` string COMMENT '',
    `updated_at` string COMMENT ''
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
  's3://glow-prod-datalake/raw/tidb/log_banks'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-prod-datalake/raw/tidb/log_banks/${dt}/'
);
