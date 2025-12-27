# DBスキーマ参照ガイド

DBスキーマJSON（`master_tables_schema.json`）の構造とjqコマンドパターン集。

## スキーマファイル

**パス**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`

**サイズ**: 471KB

---

## JSON構造

### 基本構造

```json
{
  "databases": {
    "mst": {
      "tables": {
        "mst_units": {
          "columns": {
            "id": {
              "type": "varchar(255)",
              "nullable": false,
              "comment": "ユニットID"
            },
            "rarity": {
              "type": "int",
              "nullable": false
            }
          }
        }
      }
    }
  }
}
```

### パス構造

```
.databases.mst.tables.<table_name>.columns.<column_name>
```

### カラム情報

| フィールド | 説明 | 例 |
|---------|------|-----|
| `type` | データ型 | `"int"`, `"varchar(255)"`, `"enum('A','B')"` |
| `nullable` | NULL許可 | `true`, `false` |
| `default` | デフォルト値 | `null`, `0`, `"active"` |
| `comment` | カラム説明 | `"ユニットの名前"` |

---

## jqコマンドパターン

### 1. テーブル一覧

```bash
jq '.databases.mst.tables | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 2. テーブル名検索（部分一致）

```bash
jq '.databases.mst.tables | keys | map(select(test("event"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 3. テーブル全体の構造

```bash
jq '.databases.mst.tables.mst_events' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 4. カラム一覧

```bash
jq '.databases.mst.tables.mst_events.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 5. 特定カラムの詳細

```bash
jq '.databases.mst.tables.mst_events.columns.start_at' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 6. NULL許可カラムの抽出

```bash
jq '.databases.mst.tables.mst_stages.columns | to_entries | map(select(.value.nullable == true)) | map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 7. NOT NULLカラムの抽出

```bash
jq '.databases.mst.tables.mst_stages.columns | to_entries | map(select(.value.nullable == false)) | map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 8. デフォルト値を持つカラム

```bash
jq '.databases.mst.tables.mst_stages.columns | to_entries | map(select(.value | has("default"))) | map({key: .key, default: .value.default})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 9. enum型のカラムと値

```bash
# enum型を含むカラムの型情報を確認
jq '.databases.mst.tables.opr_gachas.columns.gacha_type' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 出力例: {"type": "enum('Normal','Premium','Pickup')", "nullable": true, ...}
```

### 10. テーブルのコメント

```bash
jq '.databases.mst.tables.mst_events.comment' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 11. カラムのコメント一覧

```bash
jq '.databases.mst.tables.mst_events.columns | to_entries | map({key: .key, comment: .value.comment})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 12. 特定の型のカラムを検索

```bash
# timestampカラムを検索
jq '.databases.mst.tables.mst_events.columns | to_entries | map(select(.value.type | test("timestamp"))) | map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

---

## 実践例

### 例1: イベントテーブルの調査

```bash
# 1. イベント関連テーブルを検索
jq '.databases.mst.tables | keys | map(select(test("event"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 2. mst_eventsの構造を確認
jq '.databases.mst.tables.mst_events.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 3. 必須カラム（NOT NULL）を確認
jq '.databases.mst.tables.mst_events.columns | to_entries | map(select(.value.nullable == false)) | map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 例2: ガチャの設定可能な値を確認

```bash
# gacha_typeの型情報を確認
jq '.databases.mst.tables.opr_gachas.columns.gacha_type' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 出力: {"type": "enum('Normal','Premium','Pickup','Free','Ticket',...)", ...}
```

### 例3: テーブル横断検索

```bash
# 「reward」を含むテーブルをすべて検索
jq '.databases.mst.tables | keys | map(select(test("reward"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

---

## ヘルパースクリプト

より簡単に使えるスクリプトが用意されています：

```bash
# 使用例
.claude/skills/masterdata-explorer/scripts/search_schema.sh tables event
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_units
.claude/skills/masterdata-explorer/scripts/search_schema.sh enum opr_gachas gacha_type
```

詳細は [search_schema.sh](../scripts/search_schema.sh) を参照してください。

---

## 注意事項

### テーブル名の違い

| 種類 | 形式 | 例 |
|------|------|-----|
| **DBスキーマ** | snake_case + 複数形 | `mst_events`, `opr_gachas` |
| **CSVファイル** | PascalCase + 単数形 | `MstEvent.csv`, `OprGacha.csv` |

jqコマンドでは**DBスキーマの形式**（snake_case + 複数形）を使用してください。

### enum型の確認方法

enum型は`type`フィールドに`enum('value1','value2',...)`形式で定義されています：

```bash
jq '.databases.mst.tables.opr_gachas.columns.gacha_type.type' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 出力: "enum('Normal','Premium','Pickup','Free','Ticket','Festival','PaidOnly','Medal','Tutorial')"
```
