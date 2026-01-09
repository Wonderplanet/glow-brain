-- log_currency_paids
CREATE EXTERNAL TABLE `glow_develop_user_action_logs`.`log_currency_paids` (
    `id` string COMMENT 'UUID',
    `seq_no` bigint COMMENT '登録した連番',
    `usr_user_id` string COMMENT 'ユーザーID',
    `currency_paid_id` string COMMENT '変動した通貨テーブルのレコードID',
    `receipt_unique_id` string COMMENT 'このレコードを生成した購入レシートID（購入の場合）',
    `is_sandbox` int COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
    `query` string COMMENT 'どういう変化が起きたか',
    `purchase_price` double COMMENT '購入時の価格',
    `purchase_amount` bigint COMMENT '購入時に付与された個数',
    `price_per_amount` double COMMENT '単価',
    `vip_point` bigint COMMENT '商品購入時に獲得したVIPポイント',
    `currency_code` string COMMENT 'ISO 4217の通貨コード',
    `before_amount` bigint COMMENT '変更前の有償一次通貨の数',
    `change_amount` bigint COMMENT '取得した有償一次通貨の数
消費の場合は負',
    `current_amount` bigint COMMENT '変動後現在でユーザーがプラットフォームに所持している有償一次通貨の数
単価関係ない総数（summaryに入れる数）',
    `os_platform` string COMMENT 'OSプラットフォーム',
    `billing_platform` string COMMENT 'AppStore / GooglePlay',
    `trigger_type` string COMMENT '有償一次通貨の変動契機',
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
  's3://glow-develop-datalake/raw/tidb/log_currency_paids'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-develop-datalake/raw/tidb/log_currency_paids/${dt}/'
);
