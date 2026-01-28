-- log_stage_actions
CREATE EXTERNAL TABLE `glow_develop_user_action_logs`.`log_stage_actions` (
    `id` string COMMENT 'UUID',
    `usr_user_id` string COMMENT 'usr_users.id',
    `nginx_request_id` string COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
    `request_id` string COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
    `logging_no` int COMMENT 'APIリクエスト中でのログの順番',
    `mst_stage_id` string COMMENT 'mst_stages.id',
    `api_path` string COMMENT 'リクエストされたステージ関連のAPI',
    `result` int COMMENT 'ステージ結果。0: 結果未確定, 1: 敗北, 2: 勝利',
    `mst_outpost_id` string COMMENT '使用中のゲート',
    `mst_artwork_id` string COMMENT '装備中の原画',
    `defeat_enemy_count` int COMMENT '敵撃破数',
    `defeat_boss_enemy_count` int COMMENT 'ボス敵撃破数',
    `score` int COMMENT 'スコア',
    `clear_time_ms` string COMMENT 'クリアタイム(ミリ秒)',
    `discovered_enemies` string COMMENT '発見した敵情報',
    `party_status` string COMMENT 'パーティステータス情報',
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
  's3://glow-develop-datalake/raw/tidb/log_stage_actions'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-develop-datalake/raw/tidb/log_stage_actions/${dt}/'
);
