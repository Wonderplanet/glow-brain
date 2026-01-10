-- log_trade_shop_items
CREATE EXTERNAL TABLE `glow_develop_user_action_logs`.`log_trade_shop_items` (
    `id` string COMMENT 'ID',
    `usr_user_id` string COMMENT 'usr_users.id',
    `nginx_request_id` string COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
    `request_id` string COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
    `logging_no` int COMMENT 'APIリクエスト中でのログの順番',
    `mst_shop_item_id` string COMMENT 'mst_shop_items.id',
    `trade_count` int COMMENT '交換回数',
    `cost_type` string COMMENT '消費したリソースのタイプ',
    `cost_amount` int COMMENT '消費したリソースの数',
    `received_reward` string COMMENT '変換後の配布報酬情報(実際にユーザーが受け取った報酬情報)',
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
  's3://glow-develop-datalake/raw/tidb/log_trade_shop_items'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-develop-datalake/raw/tidb/log_trade_shop_items/${dt}/'
);
