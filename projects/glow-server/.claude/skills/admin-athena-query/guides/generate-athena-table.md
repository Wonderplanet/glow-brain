# Athenaテーブル定義の生成

## 目次

1. [コマンド概要](#コマンド概要)
2. [基本的な使い方](#基本的な使い方)
3. [コマンドオプション](#コマンドオプション)
4. [出力先](#出力先)
5. [生成されるSQLの構造](#生成されるsqlの構造)
6. [データ型の変換](#データ型の変換)
7. [対象テーブル](#対象テーブル)
8. [Athenaへのテーブル作成](#athenaへのテーブル作成)
9. [トラブルシューティング](#トラブルシューティング)

---

## コマンド概要

`app:athena:generate-table`コマンドでAthenaテーブル定義SQLを生成します。

## 基本的な使い方

### 特定テーブルのみ生成

```bash
# develop環境用
sail artisan app:athena:generate-table \
  --table=log_xxx \
  --target-env=develop \
  --database=glow_develop_user_action_logs

# production環境用
sail artisan app:athena:generate-table \
  --table=log_xxx \
  --target-env=prod \
  --database=glow_prod_user_action_logs
```

### 全テーブルを再生成

```bash
# develop環境用（全log_*テーブル）
sail artisan app:athena:generate-table \
  --target-env=develop \
  --database=glow_develop_user_action_logs

# production環境用（全log_*テーブル）
sail artisan app:athena:generate-table \
  --target-env=prod \
  --database=glow_prod_user_action_logs
```

## コマンドオプション

| オプション | 説明 | 必須 | デフォルト値 |
|-----------|------|:----:|------------|
| `--table` | 特定のテーブル名 | - | 全log/usrテーブル |
| `--target-env` | 環境名 | - | develop |
| `--bucket` | S3バケット名 | - | glow-{target-env}-datalake |
| `--database` | Athenaデータベース名 | ✅ | - |
| `--start-date` | パーティション開始日 | - | 2025/07/01 |

## 出力先

生成されたSQLファイルは以下のパスに出力されます：

```
admin/database/athena_tables/
├── develop/
│   └── glow_develop_user_action_logs/
│       └── log_xxx.sql
└── prod/
    └── glow_prod_user_action_logs/
        └── log_xxx.sql
```

## 生成されるSQLの構造

```sql
-- log_xxx
CREATE EXTERNAL TABLE `glow_develop_user_action_logs`.`log_xxx` (
    `id` string COMMENT '',
    `usr_user_id` string COMMENT 'usr_users.id',
    `nginx_request_id` string COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
    -- ... 他のカラム
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
  's3://glow-develop-datalake/raw/tidb/log_xxx'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-develop-datalake/raw/tidb/log_xxx/${dt}/'
);
```

## データ型の変換

MySQLからAthenaへの型変換は以下のルールで行われます：

| MySQL型 | Athenaでの型 | 備考 |
|---------|------------|------|
| NULLABLEなカラム | `string` | \N処理の問題回避 |
| tinyint, int | `int` | NOT NULLの場合 |
| bigint | `bigint` | NOT NULLの場合 |
| datetime, timestamp | `string` | CSVでは文字列として扱う |
| varchar, text | `string` | - |
| json | `string` | - |

## 対象テーブル

コマンドは以下のテーブルを対象とします：

1. **log_* プレフィックスのテーブル**: 全件
2. **usr_* テーブル**: `DatalakeConstant::WP_DATALAKE_S3_UPLOAD_TARGET_TABLES`に定義されたもののみ

## Athenaへのテーブル作成

**生成されたSQLはAWSコンソールで手動実行が必要です**

1. AWSコンソールにログイン
2. Athenaサービスを開く
3. 適切なワークグループを選択
4. 生成されたSQLをクエリエディタに貼り付けて実行

## トラブルシューティング

### テーブルが見つからない場合

```
テーブルが見つかりません: log_xxx
```

→ TiDBにテーブルが存在するか確認。マイグレーションを実行済みか確認。

### データベースオプション未指定エラー

```
--database オプションは必須です。
```

→ `--database=glow_develop_user_action_logs` を指定してください。
