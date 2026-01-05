-- log_pvp_actions
CREATE EXTERNAL TABLE `glow_prod_user_action_logs`.`log_pvp_actions` (
    `id` string COMMENT '',
    `usr_user_id` string COMMENT 'usr_users.id',
    `nginx_request_id` string COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
    `request_id` string COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
    `logging_no` int COMMENT 'APIリクエスト中でのログの順番',
    `sys_pvp_season_id` string COMMENT 'sys_pvp_seasons.id',
    `api_path` string COMMENT 'リクエストされたPVP関連のAPI',
    `result` int COMMENT 'PVP結果。0: 結果未確定, 1: 敗北, 2: 勝利 3: リタイア 4: 中断復帰キャンセル',
    `my_pvp_status` string COMMENT 'PVPステータス情報',
    `opponent_my_id` string COMMENT '対戦相手id',
    `opponent_pvp_status` string COMMENT 'PVPステータス情報',
    `in_game_battle_log` string COMMENT 'インゲームのバトルログ',
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
  's3://glow-prod-datalake/raw/tidb/log_pvp_actions'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-prod-datalake/raw/tidb/log_pvp_actions/${dt}/'
);
