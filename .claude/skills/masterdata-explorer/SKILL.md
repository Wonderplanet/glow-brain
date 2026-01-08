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

## ツール

マスタデータ調査を支援するツールが用意されています。

### スキーマ調査ツール（search_schema.sh）

**用途**: DBスキーマの構造を調査する（jqベース）

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

## DuckDBによるSQL分析

DuckDB CLIを使用すると、マスタデータCSVに対して高速かつ柔軟なSQL分析が可能です。

### 特徴

- ✅ **標準SQL**: 普遍的な知識で使える
- ✅ **JOIN/集計/統計**: 複雑なクエリも自由自在
- ✅ **対話モード**: REPL、ヒストリ、補完機能
- ✅ **__NULL__ハンドリング**: CSVの`__NULL__`を真のNULLとして扱える

### セットアップ

**1. DuckDBのインストール**

```bash
# インストール確認
which duckdb

# インストール（未インストールの場合）
brew install duckdb
```

**2. DuckDBの起動**

```bash
# glow-brainルートディレクトリで起動
cd /Users/junki.mizutani/Documents/workspace/glow/glow-brain

# 初期化ファイルを読み込んで起動（推奨）
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc

# または、初期化なしで起動
duckdb
```

初期化ファイル（`.duckdbrc`）を使用すると、便利な設定が自動で適用されます。

### 基本的な使い方

#### 対話モード（推奨）

```bash
# DuckDB起動
$ duckdb -init .claude/skills/masterdata-explorer/.duckdbrc

# URレア度のユニット一覧
D SELECT * FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
  WHERE ENABLE = 'e' AND rarity = 'UR' LIMIT 10;

# レア度別集計
D SELECT rarity, COUNT(*) as count
  FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
  WHERE ENABLE = 'e'
  GROUP BY rarity
  ORDER BY count DESC;

# シリーズ別ユニット数（JOIN）
D SELECT s.asset_key as series, COUNT(u.id) as units
  FROM read_csv('projects/glow-masterdata/MstSeries.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') s
  LEFT JOIN read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') u
    ON s.id = u.mst_series_id AND u.ENABLE = 'e'
  WHERE s.ENABLE = 'e'
  GROUP BY s.asset_key
  ORDER BY units DESC;

# 終了
D .quit
```

#### ワンライナー

```bash
# コマンドライン引数で直接実行
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc \
  -c "SELECT rarity, COUNT(*) FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE ENABLE = 'e' GROUP BY rarity"
```

### DuckDB便利コマンド

対話モード内で使える便利なコマンド（`.`で始まる）：

```sql
-- モード変更
.mode csv               -- CSV出力（デフォルト）
.mode table             -- テーブル形式
.mode markdown          -- Markdown表形式

-- ヘッダー表示
.headers on             -- ヘッダー表示（デフォルト）
.headers off            -- ヘッダー非表示

-- タイマー
.timer on               -- クエリ実行時間を表示

-- ヘルプ
.help                   -- コマンド一覧
```

### 実践的なクエリ例

#### 基本的な検索

```sql
-- 特定IDの検索
SELECT * FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id = 'chara_dan_00001';

-- 部分一致検索
SELECT * FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'chara_dan%';

-- ユニーク値一覧
SELECT DISTINCT rarity FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e' ORDER BY rarity;

-- レコード数
SELECT COUNT(*) FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e' AND rarity = 'UR';
```

#### JOIN分析

```sql
-- イベントとシリーズの紐付け
SELECT e.id as event_id, e.start_at, s.asset_key as series
FROM read_csv('projects/glow-masterdata/MstEvent.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') e
JOIN read_csv('projects/glow-masterdata/MstSeries.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') s
  ON e.mst_series_id = s.id
WHERE e.ENABLE = 'e' AND s.ENABLE = 'e';

-- ユニットとアビリティの結合
SELECT u.id, u.rarity, a.mst_ability_id
FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') u
LEFT JOIN read_csv('projects/glow-masterdata/MstUnitAbility.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') a
  ON u.mst_unit_ability_id1 = a.id
WHERE u.ENABLE = 'e' AND u.rarity = 'UR';
```

#### 整合性チェック

```sql
-- 報酬アイテムの存在確認
SELECT
  r.resource_id,
  CASE WHEN i.id IS NULL THEN '❌ NOT FOUND' ELSE '✅ OK' END as status
FROM read_csv('projects/glow-masterdata/MstStageReward.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') r
LEFT JOIN read_csv('projects/glow-masterdata/MstItem.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') i
  ON r.resource_id = i.id AND r.resource_type = 'Item'
WHERE r.ENABLE = 'e'
GROUP BY r.resource_id, i.id
HAVING status = '❌ NOT FOUND';
```

#### 集計・統計

```sql
-- レア度別分布（パーセンテージ付き）
SELECT
  rarity,
  COUNT(*) as count,
  ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage
FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
GROUP BY rarity
ORDER BY count DESC;

-- ウィンドウ関数（ランキング）
SELECT
  id,
  rarity,
  max_attack_power,
  RANK() OVER (PARTITION BY rarity ORDER BY max_attack_power DESC) as rank_in_rarity
FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
ORDER BY rarity, rank_in_rarity
LIMIT 20;
```

### マクロなしでの使い方

初期化ファイルを使わない場合は、`read_csv()`を直接使用します：

```sql
SELECT * FROM read_csv(
  'projects/glow-masterdata/MstUnit.csv',
  AUTO_DETECT=TRUE,
  nullstr='__NULL__'
)
WHERE ENABLE = 'e' AND rarity = 'UR';
```

### クエリパターン集

20以上の実践的なクエリパターンは [duckdb-query-examples.md](references/duckdb-query-examples.md) を参照してください。

---

## 機能の使い分けガイド

| 用途 | 推奨ツール | 理由 |
|------|----------|------|
| テーブル名検索 | `search_schema.sh tables` | スキーマJSONから検索、軽量 |
| カラム名一覧 | `search_schema.sh columns` | スキーマ定義を参照 |
| enum値の確認 | `search_schema.sh enum` | 許可値の確認に最適 |
| 1-2行の確認 | `grep`/`head` | 高速、シンプル |
| **条件検索** | **DuckDB** | WHERE句で柔軟な検索 |
| **JOIN分析** | **DuckDB** | 複数テーブルの関連分析 |
| **集計・統計** | **DuckDB** | COUNT、GROUP BY、ウィンドウ関数など |

### 基本的な判断基準

1. **スキーマ構造を知りたい** → `search_schema.sh`
2. **データ内容を見たい** → DuckDB または `grep`/`head`
3. **複数テーブルを分析したい** → DuckDB

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

より詳細な情報については、以下を参照してください：

- [schema-reference.md](references/schema-reference.md) - jqパターンとスキーマ構造
- [duckdb-query-examples.md](references/duckdb-query-examples.md) - DuckDBクエリパターン集（20+例）
