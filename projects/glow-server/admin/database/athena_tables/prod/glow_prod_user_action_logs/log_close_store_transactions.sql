-- log_close_store_transactions
CREATE EXTERNAL TABLE `glow_prod_user_action_logs`.`log_close_store_transactions` (
    `id` string COMMENT '',
    `usr_user_id` string COMMENT 'ユーザーID',
    `platform_product_id` string COMMENT 'プラットフォーム側で定義しているproduct_id',
    `mst_store_product_id` string COMMENT 'マスターテーブルのプロダクトID',
    `product_sub_id` string COMMENT '購入対象のproduct_sub_id',
    `product_sub_name` string COMMENT '実際の販売商品名',
    `raw_receipt` string COMMENT '復号済み生レシートデータ',
    `raw_price_string` string COMMENT 'クライアントから送られてきた単価付き購入価格',
    `currency_code` string COMMENT 'ISO 4217の通貨コード',
    `receipt_unique_id` string COMMENT 'レシート記載、ユニークなID',
    `receipt_bundle_id` string COMMENT 'レシート記載、ストアから送られてきた商品のバンドルID',
    `os_platform` string COMMENT 'OSプラットフォーム',
    `billing_platform` string COMMENT 'AppStore / GooglePlay',
    `device_id` string COMMENT 'ユーザーの使用しているデバイス識別子',
    `purchase_price` string COMMENT 'ストアから送られてきた実際の購入価格',
    `is_sandbox` int COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
    `log_store_id` string COMMENT '失敗したストア購入ログのレコードID',
    `usr_store_product_history_id` string COMMENT '失敗したストア商品購入テーブルのレコードID',
    `trigger_type` string COMMENT 'ロギング契機',
    `trigger_name` string COMMENT 'ロギング契機の日本語名',
    `trigger_id` string COMMENT 'ロギング契機に対応するID',
    `trigger_detail` string COMMENT 'その他の付与情報 (JSON)',
    `request_id_type` string COMMENT 'リクエスト識別IDの種類',
    `request_id` string COMMENT 'リクエスト識別ID',
    `nginx_request_id` string COMMENT 'nginxのリクエスト識別ID',
    `created_at` string COMMENT '作成日時',
    `updated_at` string COMMENT '更新日時'
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
  's3://glow-prod-datalake/raw/tidb/log_close_store_transactions'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-prod-datalake/raw/tidb/log_close_store_transactions/${dt}/'
);
