-- usr_users
CREATE EXTERNAL TABLE `glow_prod_user_action_logs`.`usr_users` (
    `id` string COMMENT 'UUID',
    `status` int COMMENT 'ユーザーステータス 0:通常プレイ可 1:時限BAN 2:永久BAN',
    `tutorial_status` string COMMENT 'チュートリアルステータス',
    `tos_version` int COMMENT '同意した利用規約のバージョン 同意モジュールを使っているので未使用列',
    `privacy_policy_version` int COMMENT '同意したプライバシーポリシーのバージョン 同意モジュールを使っているので未使用列',
    `global_consent_version` int COMMENT 'グローバルコンセントバージョン',
    `iaa_version` int COMMENT 'iaaバージョン',
    `bn_user_id` string COMMENT 'BNIDユーザーID',
    `is_account_linking_restricted` int COMMENT 'マルチログイン制限フラグ',
    `client_uuid` string COMMENT 'クライアントUUID',
    `suspend_end_at` string COMMENT '利用停止状態の終了日時',
    `game_start_at` string COMMENT 'ゲーム開始日時',
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
  's3://glow-prod-datalake/raw/tidb/usr_users'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-prod-datalake/raw/tidb/usr_users/${dt}/'
);
