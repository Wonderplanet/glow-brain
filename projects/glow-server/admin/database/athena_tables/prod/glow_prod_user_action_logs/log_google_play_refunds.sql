-- log_google_play_refunds
CREATE EXTERNAL TABLE `glow_prod_user_action_logs`.`log_google_play_refunds` (
    `id` string COMMENT '',
    `transaction_id` string COMMENT '課金のトランザクションID',
    `price` string COMMENT '返金金額',
    `refunded_at` string COMMENT '返金日時',
    `purchase_token` string COMMENT '署名付きの返金通知',
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
  's3://glow-prod-datalake/raw/tidb/log_google_play_refunds'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-prod-datalake/raw/tidb/log_google_play_refunds/${dt}/'
);
