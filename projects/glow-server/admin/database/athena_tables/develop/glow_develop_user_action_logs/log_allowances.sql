-- log_allowances
CREATE EXTERNAL TABLE `glow_develop_user_action_logs`.`log_allowances` (
    `id` string COMMENT 'UUID',
    `usr_user_id` string COMMENT 'usr_users.id',
    `product_sub_id` string COMMENT '購入対象のproduct_sub_id',
    `os_platform` string COMMENT 'OSプラットフォーム',
    `product_id` string COMMENT 'ストアのプロダクトID',
    `mst_store_product_id` string COMMENT 'mst_store_product_id',
    `billing_platform` string COMMENT 'AppStore / GooglePlay のどちらで購入フローをしようとしているか',
    `device_id` string COMMENT 'ユーザーの使用しているデバイス識別子',
    `trigger_type` string COMMENT 'alllowanceの変更契機',
    `trigger_id` string COMMENT '対象のallowanceのID',
    `trigger_name` string COMMENT '変動契機の日本語名',
    `trigger_detail` string COMMENT 'そのほかの付与情報',
    `request_id_type` string COMMENT 'リクエスト識別IDの種類',
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
  's3://glow-develop-datalake/raw/tidb/log_allowances'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-develop-datalake/raw/tidb/log_allowances/${dt}/'
);
