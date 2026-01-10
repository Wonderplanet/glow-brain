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

> **⚠️ 注意**: クエリを書く前に、必ず `search_schema.sh columns` でカラム名を確認してください。
> 詳細は [DuckDBクエリを書く前の必須チェック](#duckdbクエリを書く前の必須チェック) を参照。

```bash
# 例：MstUnitのカラム名を事前確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_units

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

## DuckDBクエリを書く前の必須チェック

### ⚠️ 重要：カラム名の事前確認は必須です

DuckDBクエリを書く前に、**必ず実際のカラム名を確認してください**。カラム名の推測は禁止です。

#### 推奨フロー

```bash
# ステップ1: テーブル名を確認（DBスキーマの形式: snake_case + 複数形）
.claude/skills/masterdata-explorer/scripts/search_schema.sh tables unit

# ステップ2: カラム名の一覧を取得
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_units

# ステップ3: 必要に応じてenum値を確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh enum mst_units rarity

# ステップ4: CSVファイル名に変換（PascalCase + 単数形）して、DuckDBクエリを実行
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc -c "
SELECT fragment_mst_item_id, rarity
FROM read_csv('projects/glow-masterdata/MstUnit.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
LIMIT 10;
"
```

### よくあるエラーパターンと回避方法

| エラーパターン | 原因 | 回避方法 |
|-------------|------|---------|
| `Table does not have a column named "piece_asset_key"` | カラム名の推測ミス | `search_schema.sh columns` で実際のカラム名を確認 |
| `Table does not have a column named "available_from"` | カラム名の推測ミス | DBスキーマでは `start_date` や `start_at` が使われることが多い |
| `No files found that match the pattern` | CSVファイル名の誤り | DBスキーマ名（snake_case複数形）→ CSV名（PascalCase単数形）に変換 |
| `Binder Error: Table "x" does not have a column` | JOINで存在しないカラムを参照 | 各テーブルのカラム名を個別に確認 |

### カラム名確認のショートカット

よく使うテーブルのカラム確認：

```bash
# ユニット（キャラクター）のカラム
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_units

# アイテムのカラム
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_items

# イベントのカラム
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_events

# ガチャのカラム
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns opr_gachas
```

### 安全なクエリ実行の3ステップ

1. **スキーマ確認**: `search_schema.sh columns` でカラム名を取得
2. **テスト実行**: `LIMIT 10` でまず少量のデータで動作確認
3. **本番実行**: エラーがなければ、全データでクエリを実行

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

### カラム名のエラー

**エラー例**:
```
Binder Error: Table "u" does not have a column named "piece_asset_key"
```

**解決方法**:
```bash
# 1. 実際のカラム名を確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_units | grep piece
# または
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_units | grep fragment

# 2. 正しいカラム名でクエリを修正
# 例: piece_asset_key → fragment_mst_item_id
```

**予防策**: [DuckDBクエリを書く前の必須チェック](#duckdbクエリを書く前の必須チェック) を参照して、クエリを書く前に必ずカラム名を確認してください。

---

## Enum検知機能

varchar列で使用されているEnum値を検出・分析する機能です。

### 背景

DBスキーマでは`varchar(255)`として定義されていますが、実際にはEnum的な固定値セットを格納しているカラムが多数存在します。これらのEnum定義は以下で管理されています：

- **スキーマ定義**: `projects/glow-schema/Schema/*.yml`
- **サーバー側（PHP）**: `projects/glow-server/api/app/Domain/*/Enums/*.php`
- **クライアント側（C#）**: `projects/glow-client/Assets/GLOW/Scripts/Runtime/Core/Domain/Constants/AutoGenerated/*.cs`

### 使用方法

```bash
# Enum一覧を取得
.claude/skills/masterdata-explorer/scripts/enum_detector.sh list-enums schema

# カラムに対応するEnum情報を検索
.claude/skills/masterdata-explorer/scripts/enum_detector.sh find-enum MstUnit roleType

# PHP Enumから値を抽出
.claude/skills/masterdata-explorer/scripts/enum_detector.sh php-enum GachaType

# C# Enumから値を抽出
.claude/skills/masterdata-explorer/scripts/enum_detector.sh csharp-enum ItemType

# データ定義内の全Enum列を検出
.claude/skills/masterdata-explorer/scripts/enum_detector.sh detect OprGacha

# サーバー/クライアント間のEnum値整合性チェック
.claude/skills/masterdata-explorer/scripts/enum_detector.sh compare ItemType
```

### コマンド詳細

| コマンド | 説明 | 使用例 |
|---------|------|--------|
| `list-enums [schema\|php\|csharp]` | Enum一覧を取得 | `list-enums schema` |
| `find-enum <DataName> <columnName>` | カラムのEnum情報を表示 | `find-enum MstUnit roleType` |
| `php-enum <EnumName>` | PHP Enum値を抽出 | `php-enum GachaType` |
| `csharp-enum <EnumName>` | C# Enum値を抽出 | `csharp-enum ItemType` |
| `detect <DataName>` | データ内の全Enum列を検出 | `detect OprGacha` |
| `compare <EnumName>` | PHP/C#間の値を比較 | `compare ItemType` |

### ユースケース

#### 1. マスタデータ作成時に入力可能な値を確認

```bash
# MstUnit.roleType に入力可能な値を確認
.claude/skills/masterdata-explorer/scripts/enum_detector.sh find-enum MstUnit roleType
```

出力例:
```
Column: MstUnit.roleType

  Enum Type: CharacterUnitRoleType

  PHP File: projects/glow-server/api/app/Domain/Unit/Enums/RoleType.php
  PHP Values:
    Attacker
    Tank
    Healer
    Support

  C# File: projects/glow-client/Assets/.../CharacterUnitRoleType.cs
  C# Values:
    Attacker
    Tank
    Healer
    Support
```

#### 2. サーバーとクライアントの整合性確認

```bash
# ItemTypeの値がサーバーとクライアントで一致しているか確認
.claude/skills/masterdata-explorer/scripts/enum_detector.sh compare ItemType
```

出力例:
```
Comparing ItemType...

PHP only:
  (none)

C# only:
  (none)

Common:
  CharacterFragment
  RankUpMaterial
  RankUpMemoryFragment
  ...
```

#### 3. テーブル内の全Enum列を把握

```bash
# OprGachaテーブルで使用されているEnum列を一覧表示
.claude/skills/masterdata-explorer/scripts/enum_detector.sh detect OprGacha
```

出力例:
```
Detecting enum columns in OprGacha...

  gachaType -> GachaType (PHP/C#)
  appearanceCondition -> AppearanceCondition (PHP/C#)
  unlockConditionType -> GachaUnlockConditionType (PHP/C#)
```

### 依存ツール

- `yq`: YAML処理用（glow-schema読み取り）

インストール:
```bash
brew install yq
```
