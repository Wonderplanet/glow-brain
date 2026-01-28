-- log_stores
CREATE EXTERNAL TABLE `glow_prod_user_action_logs`.`log_stores` (
    `id` string COMMENT 'UUID',
    `seq_no` string COMMENT '登録した連番',
    `usr_user_id` string COMMENT 'usr_users.id',
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
    `age` int COMMENT '年齢',
    `paid_amount` bigint COMMENT '有償一次通貨の付与量',
    `free_amount` bigint COMMENT '無償一次通貨の付与量',
    `purchase_price` double COMMENT 'ストアから送られてきた実際の購入価格',
    `price_per_amount` double COMMENT '単価',
    `vip_point` bigint COMMENT '商品購入時に獲得したVIPポイント',
    `is_sandbox` int COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
    `trigger_type` string COMMENT 'ショップ購入契機',
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
  's3://glow-prod-datalake/raw/tidb/log_stores'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-prod-datalake/raw/tidb/log_stores/${dt}/'
);
