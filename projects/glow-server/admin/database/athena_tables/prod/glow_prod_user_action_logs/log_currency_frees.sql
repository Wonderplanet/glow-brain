-- log_currency_frees
CREATE EXTERNAL TABLE `glow_prod_user_action_logs`.`log_currency_frees` (
    `id` string COMMENT 'UUID',
    `logging_no` bigint COMMENT 'ログ登録番号',
    `usr_user_id` string COMMENT 'ユーザーID',
    `os_platform` string COMMENT 'OSプラットフォーム',
    `before_ingame_amount` bigint COMMENT '変更前のゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の数',
    `before_bonus_amount` bigint COMMENT '変更前のショップ販売の追加ボーナスで取得した無償一次通貨の数',
    `before_reward_amount` bigint COMMENT '変更前の広告視聴等の報酬で取得した無償一次通貨の数',
    `change_ingame_amount` bigint COMMENT 'ゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の数
消費の場合は負',
    `change_bonus_amount` bigint COMMENT 'ショップ販売の追加ボーナスで取得した無償一次通貨の数
消費の場合は負',
    `change_reward_amount` bigint COMMENT '広告視聴等の報酬で取得した無償一次通貨の数
消費の場合は負',
    `current_ingame_amount` bigint COMMENT 'ゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の現在の数',
    `current_bonus_amount` bigint COMMENT 'ショップ販売の追加ボーナスで取得した無償一次通貨の現在の数',
    `current_reward_amount` bigint COMMENT '広告視聴等の報酬で取得した無償一次通貨の現在の数',
    `trigger_type` string COMMENT '無償一次通貨の変動契機',
    `trigger_id` string COMMENT '変動契機に対応するID',
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
  's3://glow-prod-datalake/raw/tidb/log_currency_frees'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-prod-datalake/raw/tidb/log_currency_frees/${dt}/'
);
