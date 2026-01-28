-- log_advent_battle_actions
CREATE EXTERNAL TABLE `glow_prod_user_action_logs`.`log_advent_battle_actions` (
    `id` string COMMENT 'UUID',
    `usr_user_id` string COMMENT 'usr_users.id',
    `nginx_request_id` string COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
    `request_id` string COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
    `logging_no` int COMMENT 'APIリクエスト中でのログの順番',
    `mst_advent_battle_id` string COMMENT 'mst_advent_battles.id',
    `api_path` string COMMENT 'リクエストされた降臨バトル関連のAPI',
    `result` int COMMENT 'ステージ結果。0: 結果未確定, 1: 敗北, 2: 勝利',
    `party_units` string COMMENT 'ユニットのステータス情報を含めたパーティ情報',
    `used_outpost` string COMMENT '使用したゲート情報',
    `in_game_battle_log` string COMMENT 'インゲームのバトルログ',
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
  's3://glow-prod-datalake/raw/tidb/log_advent_battle_actions'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-prod-datalake/raw/tidb/log_advent_battle_actions/${dt}/'
);
