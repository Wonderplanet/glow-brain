---
name: masterdata-explorer
description: GLOWマスタデータのスキーマ調査とSQL分析。jqでテーブル構造確認、DuckDBでCSVクエリ。マスタデータ、CSV、スキーマで使用。
---

# GLOWマスタデータ調査スキル

GLOWプロジェクトのマスタデータを効率的に調査・理解するためのスキルです。

**このスキルは参照専用**です。データ作成・編集は行いません。

---

## クイックスタート

### 1. スキーマ調査（jqでテーブル構造確認）

```bash
# テーブル一覧
.claude/skills/masterdata-explorer/scripts/search_schema.sh tables event

# カラム一覧
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_units

# enum値確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh enum opr_gachas gacha_type
```

### 2. データ分析（DuckDBでSQLクエリ）

```bash
# DuckDB起動
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc

# レア度別集計
SELECT rarity, COUNT(*) as count
FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
GROUP BY rarity
ORDER BY count DESC;

# シリーズ別ユニット数（JOIN）
SELECT s.asset_key as series, COUNT(u.id) as units
FROM read_csv('projects/glow-masterdata/MstSeries.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') s
LEFT JOIN read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') u
  ON s.id = u.mst_series_id AND u.ENABLE = 'e'
WHERE s.ENABLE = 'e'
GROUP BY s.asset_key
ORDER BY units DESC;
```

---

## 3つのデータソース

### 1. DBスキーマ（構造・制約の確認）

**パス**: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`

**用途**: テーブル構造、カラムの型、enum値の確認

### 2. CSVテンプレート（列順の確認）

**パス**: `projects/glow-masterdata/sheet_schema/`

**用途**: マスタデータ作成時のテンプレート

### 3. 既存マスタデータCSV（実データ例）

**パス**: `projects/glow-masterdata/*.csv`

**用途**: 過去に作成済みの既存マスタデータ（参考用）

---

## テーブル命名規則

| 種類 | 命名規則 | 例 |
|------|---------|-----|
| **DBスキーマ** | snake_case + 複数形 | `mst_events`, `opr_gachas` |
| **CSVファイル** | PascalCase + 単数形 | `MstEvent.csv`, `OprGacha.csv` |

**プレフィックス**:
- `mst_*` - 固定マスタデータ
- `opr_*` - 運営施策・期間限定データ

---

## 使い分けガイド

| 用途 | 推奨ツール | 理由 |
|------|----------|------|
| テーブル名検索 | `search_schema.sh tables` | スキーマJSONから検索、軽量 |
| カラム名一覧 | `search_schema.sh columns` | スキーマ定義を参照 |
| enum値の確認 | `search_schema.sh enum` | 許可値の確認に最適 |
| 1-2行の確認 | `grep`/`head` | 高速、シンプル |
| **条件検索** | **DuckDB** | WHERE句で柔軟な検索 |
| **JOIN分析** | **DuckDB** | 複数テーブルの関連分析 |
| **集計・統計** | **DuckDB** | COUNT、GROUP BY、ウィンドウ関数など |

---

## 詳細ドキュメント

より詳細な情報については、以下を参照してください：

- **[schema-reference.md](references/schema-reference.md)** - jqパターンとスキーマ構造の詳細
- **[duckdb-query-examples.md](references/duckdb-query-examples.md)** - DuckDBクエリパターン集（20+例）
- **[masterdata-guide.md](references/masterdata-guide.md)** - マスタデータの詳細ガイド

---

## トラブルシューティング

### DuckDBが起動しない

```bash
# インストール確認
which duckdb

# インストール（未インストールの場合）
brew install duckdb
```

### CSVファイルが見つからない

```bash
# glow-brainルートから起動しているか確認
pwd
# /Users/junki.mizutani/Documents/workspace/glow/glow-brain

# setup.shを実行してプロジェクトをクローン
./scripts/setup.sh
```

### テーブル名の大文字小文字エラー

- **DBスキーマ**: snake_case + 複数形（例: `mst_events`）
- **CSVファイル**: PascalCase + 単数形（例: `MstEvent.csv`）
