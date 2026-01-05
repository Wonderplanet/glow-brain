---
name: masterdata-explorer
description: GLOWマスタデータのDBスキーマとCSVファイルを調査・理解するためのスキル。jqコマンドでテーブル構造を確認し、既存データを参照する。
---

# GLOWマスタデータ調査スキル

GLOWプロジェクトのマスタデータを効率的に調査・理解するためのガイド。

## スキルの目的

マスタデータの**調査・理解を支援**します：
- テーブル構造の確認（DBスキーマをjqで参照）
- 既存データの参照（CSVファイルを直接確認）
- テーブル名・カラム名の検索

**このスキルは参照専用**です。データ作成・編集は行いません。

---

## 3つのデータソース

### 1. DBスキーマ（構造・制約の確認）

**パス**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`

**用途**: 入力データのバリデーション（型、NULL許可、enum値等）に使用する。

**取得できる情報**:
- テーブル・カラムの一覧
- カラムの型、NULL許可、デフォルト値
- enum型の許可値

**基本コマンド**:
```bash
# テーブル一覧
jq '.databases.mst.tables | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# テーブル構造
jq '.databases.mst.tables.mst_events' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# カラム一覧
jq '.databases.mst.tables.mst_events.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

---

### 2. CSVテンプレート（列順の確認）

**パス**: `projects/glow-masterdata/sheet_schema/`

**用途**: 作成するマスタデータのCSVテンプレート。ヘッダー行の列順・列名を定義。このテンプレートをコピーしてマスタデータを作成する（列順・列名は変更禁止）。

**基本コマンド**:
```bash
head -3 projects/glow-masterdata/sheet_schema/MstEvent.csv
```

---

### 3. 既存マスタデータCSV（実データ例）

**パス**: `projects/glow-masterdata/*.csv`

**用途**: 過去に作成済みの既存マスタデータ（参考用）。テーブルごとのデータの作り方や値の傾向を把握するために参照する。

**基本コマンド**:
```bash
# 最初の20行を確認
head -20 projects/glow-masterdata/MstEvent.csv

# 特定ID検索
grep "^e,event_001" projects/glow-masterdata/MstEvent.csv
```

---

## テーブル命名規則

### プレフィックス

| プレフィックス | 意味 | 例 |
|-------------|------|-----|
| `mst_*` | 固定マスタデータ | `mst_units`, `mst_stages` |
| `opr_*` | 運営施策・期間限定データ | `opr_gachas`, `opr_campaigns` |

### サフィックス

| サフィックス | 意味 | 例 |
|-----------|------|-----|
| `*_i18n` | 多言語対応テーブル | `mst_units_i18n`, `mst_events_i18n` |

### DBスキーマとCSVファイル名の違い

| 種類 | 命名規則 | 例 |
|------|---------|-----|
| **DBスキーマ** | snake_case + 複数形 | `mst_events`, `opr_gachas` |
| **CSVファイル** | PascalCase + 単数形 | `MstEvent.csv`, `OprGacha.csv` |

---

## 基本的な調査フロー

### 1. テーブル名の検索

```bash
# 「event」を含むテーブルを検索
jq '.databases.mst.tables | keys | map(select(test("event"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 2. テーブル構造の確認

```bash
# テーブル全体の構造
jq '.databases.mst.tables.mst_events' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 特定カラムの詳細
jq '.databases.mst.tables.mst_events.columns.start_at' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 3. 既存データの確認

```bash
# CSVファイル一覧
ls projects/glow-masterdata/*.csv | grep -i event

# データ内容確認
head -20 projects/glow-masterdata/MstEvent.csv
```

---

## よく使うjqパターン

### テーブル一覧取得

```bash
jq '.databases.mst.tables | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### カラム一覧取得

```bash
jq '.databases.mst.tables.mst_units.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### enum値の確認

```bash
jq '.databases.mst.tables.opr_gachas.columns.gacha_type' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### NULL許可カラムの抽出

```bash
jq '.databases.mst.tables.mst_stages.columns | to_entries | map(select(.value.nullable == true)) | map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### テーブル名部分一致検索

```bash
jq '.databases.mst.tables | keys | map(select(test("gacha"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

---

## 既存リソース確認パターン

マスタデータ作成時に、報酬として設定するリソース（キャラクター、アイテム等）の存在を確認するためのパターン。

### キャラクターID検索

```bash
# 全キャラクターのID一覧（先頭20件）
head -20 projects/glow-masterdata/MstUnit.csv | cut -d',' -f2

# 特定IDの存在確認（exactマッチ）
grep "^e,chara_glo_00001," projects/glow-masterdata/MstUnit.csv

# 部分一致検索（gloシリーズのキャラ全件）
grep "^e,chara_glo_" projects/glow-masterdata/MstUnit.csv

# 作品名を含むIDの検索
grep "^e,chara_100kano_" projects/glow-masterdata/MstUnit.csv
```

### アイテムID検索

```bash
# かけらアイテム一覧
grep "^e,piece_" projects/glow-masterdata/MstItem.csv

# 特定タイプのアイテム検索（CharacterFragment）
grep "CharacterFragment" projects/glow-masterdata/MstItem.csv

# アイテムID存在確認
grep "^e,piece_glo_00001," projects/glow-masterdata/MstItem.csv
```

### リソース検索の手順

#### Step 1: 01_概要ファイルを確認

```bash
# 01_概要ファイルを確認
cat "マスタデータ/施策/${施策名}/要件/${施策名}_仕様書_01_概要.csv"
```

#### Step 2: 報酬一覧ファイルを確認（存在する場合）

```bash
# 報酬一覧ファイルを確認
cat "マスタデータ/施策/${施策名}/要件/${施策名}_仕様書_05_報酬一覧.csv"
```

#### Step 3: 他の要件CSVファイルを検索

```bash
# 施策ディレクトリ内の全要件CSVからキャラクターIDを検索
grep -r "chara_100kano_00001" "マスタデータ/施策/${施策名}/要件/"

# アイテムIDを検索
grep -r "item_xxxxx" "マスタデータ/施策/${施策名}/要件/"*.csv
```

#### Step 4: 既存マスタデータを検索

```bash
# キャラクター
grep "^e,chara_100kano_00001," projects/glow-masterdata/MstUnit.csv

# アイテム
grep "^e,item_xxxxx," projects/glow-masterdata/MstItem.csv
```

### 報酬設定で使える検索

```bash
# ガチャ報酬で使われているキャラクター一覧
grep "^e," projects/glow-masterdata/OprGachaPrize.csv | cut -d',' -f5 | sort -u

# ミッション報酬で使われているアイテム一覧
grep "^e," projects/glow-masterdata/MstMissionEventReward.csv | cut -d',' -f4 | sort -u
```

### 類似IDの検索（リソースが見つからない場合）

```bash
# 作品名を含むIDを全検索（例: 100kano）
grep "100kano" projects/glow-masterdata/MstUnit.csv

# 特定のプレフィックスで検索
grep "^e,chara_" projects/glow-masterdata/MstUnit.csv | grep "100kano"
```

---

## ヘルパースクリプト

便利なスクリプトが用意されています：

```bash
# テーブル検索
.claude/skills/masterdata-explorer/scripts/search_schema.sh tables event

# カラム一覧
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_units

# enum値確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh enum opr_gachas gacha_type
```

詳細なjqパターンは [schema-reference.md](references/schema-reference.md) を参照してください。

---

## トラブルシューティング

### テーブル名の大文字小文字エラー

**エラー**: `jq '.databases.mst.tables.MstEvent'` → `null`

**原因**: DBスキーマはsnake_case + 複数形

**修正**: `MstEvent` → `mst_events`

### CSVファイルが見つからない

**エラー**: `cat projects/glow-masterdata/mst_events.csv` → 見つからない

**原因**: CSVファイルはPascalCase + 単数形

**修正**: `mst_events.csv` → `MstEvent.csv`

---

## 詳細ドキュメント

より詳細なjqパターンとスキーマ構造については：

[schema-reference.md](references/schema-reference.md)
