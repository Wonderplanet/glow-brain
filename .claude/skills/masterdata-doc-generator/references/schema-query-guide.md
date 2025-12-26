# スキーマ取得ガイド

## masterdata-schema-inspectorスキルの使用

`masterdata-schema-inspector`スキルを使用してテーブルスキーマ情報を取得します。

### 使用方法

```
Skill(skill: "masterdata-schema-inspector", args: "MstMissionDaily")
```

### 出力例

スキルは以下の情報を返します：
- テーブル名
- 列名、型、NULL許容、デフォルト値、コメント
- ENUM型の場合は選択肢

## master_tables_schema.jsonの直接読み取り

より詳細な情報が必要な場合は、JSONファイルを直接読み取ります。

### ファイル構造

```json
{
  "databases": {
    "mst": {
      "tables": {
        "mst_mission_dailies": {
          "comment": "デイリーミッションの設定",
          "columns": {
            "id": {
              "type": "varchar(255)",
              "nullable": false,
              "comment": "UUID"
            },
            "criterion_type": {
              "type": "varchar(255)",
              "nullable": false,
              "comment": "達成条件タイプ"
            }
          }
        }
      }
    }
  }
}
```

### jqでの抽出例

```bash
# 特定テーブルの取得
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  | jq '.databases.mst.tables.mst_mission_dailies'

# テーブル名一覧の取得
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  | jq '.databases.mst.tables | keys[]'

# ミッション関連テーブルの検索
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  | jq '.databases.mst.tables | keys[]' | grep -i mission
```

## sheet_schemaの確認

シートスキーマは`projects/glow-masterdata/sheet_schema/`ディレクトリにあります。

### ファイル形式

```csv
memo
TABLE,MstMissionDaily,MstMissionDaily,...
ENABLE,id,release_key,criterion_type,criterion_value,...
```

- 1行目: memo
- 2行目: テーブル名の指定
- 3行目: 実際の列名（ヘッダー）

### 検索方法

```bash
# ミッション関連のシートを検索
ls projects/glow-masterdata/sheet_schema/ | grep -i mission

# ヘッダー（3行目）を表示
sed -n '3p' projects/glow-masterdata/sheet_schema/MstMissionDaily.csv
```

## ENUM値の抽出

サーバーコードからENUM定義を抽出します。

### ENUMファイルの場所

`projects/glow-server/api/app/Domain/*/Enums/*.php`

### 抽出方法

extract_enums.pyスクリプトを使用：

```bash
python3 scripts/extract_enums.py \
  projects/glow-server/api/app/Domain/Mission/Enums/MissionCriterionType.php
```

### 手動抽出（Readツールを使用）

```
Read(file_path: "projects/glow-server/api/app/Domain/Mission/Enums/MissionCriterionType.php")
```

ENUM値は以下のパターンで定義されています：

```php
enum MissionCriterionType: string
{
    case LOGIN_COUNT = 'LoginCount';
    case STAGE_CLEAR_COUNT = 'StageClearCount';
    // ...
}
```

`case XXX = 'ActualValue';`の`ActualValue`部分が実際に使用される値です。

## 実マスタデータの確認

### ファイルの場所

`projects/glow-masterdata/*.csv`

### サンプリング方法

sample_masterdata.pyスクリプトを使用：

```bash
python3 scripts/sample_masterdata.py \
  projects/glow-masterdata/MstMissionDaily.csv --limit 5
```

### 手動確認

```
Read(file_path: "projects/glow-masterdata/MstMissionDaily.csv", limit: 10)
```

マスタデータCSVは標準的なCSV形式：
- 1行目: ヘッダー
- 2行目以降: データ

注意: `sheet_schema/` ディレクトリのCSVファイルは異なる形式（4行形式）です。
