-- log_idle_incentive_rewards
CREATE EXTERNAL TABLE `glow_prod_user_action_logs`.`log_idle_incentive_rewards` (
    `id` string COMMENT 'ID',
    `usr_user_id` string COMMENT 'usr_users.id',
    `nginx_request_id` string COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
    `request_id` string COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
    `logging_no` int COMMENT 'APIリクエスト中でのログの順番',
    `exec_method` string COMMENT '探索報酬の受け取り方法',
    `idle_started_at` string COMMENT '放置開始日時',
    `elapsed_minutes` int COMMENT '報酬計算に使用した放置時間(分)',
    `received_reward` string COMMENT '配布報酬情報(変換前情報あり)',
    `received_reward_at` string COMMENT '探索報酬を受け取った日時',
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
  's3://glow-prod-datalake/raw/tidb/log_idle_incentive_rewards'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-prod-datalake/raw/tidb/log_idle_incentive_rewards/${dt}/'
);
