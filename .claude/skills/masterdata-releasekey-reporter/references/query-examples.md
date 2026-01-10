# Masterdata Release Key Reporter - クエリ例

このドキュメントでは、リリースキーデータを分析するための便利なDuckDBクエリパターンを紹介します。

## 基本的な使い方

### query_release.shスクリプト経由

```bash
# 特定テーブルの全データ表示
./scripts/query_release.sh 202512020 table MstEvent

# カテゴリ別テーブル一覧
./scripts/query_release.sh 202512020 category Mst
./scripts/query_release.sh 202512020 category Opr
./scripts/query_release.sh 202512020 category I18n

# パターン検索（IDやasset_keyで）
./scripts/query_release.sh 202512020 search quest_raid

# 統計情報の表示
./scripts/query_release.sh 202512020 stats

# 任意のSQLクエリ
./scripts/query_release.sh 202512020 sql "SELECT COUNT(*) FROM read_csv('マスタデータ/リリース/202512020/tables/MstEvent.csv', AUTO_DETECT=TRUE)"
```

### DuckDB直接起動

```bash
# 初期化ファイルを使って起動
duckdb -init .claude/skills/masterdata-releasekey-reporter/.duckdbrc
```

---

## クエリパターン集

### 1. 基本的なクエリ

#### テーブルの全データ取得

```sql
SELECT *
FROM read_csv('マスタデータ/リリース/202512020/tables/MstEvent.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
LIMIT 10;
```

#### 特定IDの完全一致検索

```sql
SELECT *
FROM read_csv('マスタデータ/リリース/202512020/tables/MstEvent.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id = 'event_osh_00001';
```

#### LIKE検索（部分一致）

```sql
SELECT *
FROM read_csv('マスタデータ/リリース/202512020/tables/MstUnit.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE '%osh%';
```

#### 行数カウント

```sql
SELECT COUNT(*) as total_rows
FROM read_csv('マスタデータ/リリース/202512020/tables/MstAdventBattle.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__');
```

#### 日付範囲検索

```sql
SELECT id, start_at, end_at
FROM read_csv('マスタデータ/リリース/202512020/tables/MstEvent.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE start_at >= '2026-01-01'
  AND end_at <= '2026-02-28';
```

---

### 2. JOIN分析

#### イベントとシリーズの紐付け

```sql
SELECT
  e.id as event_id,
  e.mst_series_id,
  e.start_at,
  e.end_at,
  s.id as series_id
FROM read_csv('マスタデータ/リリース/202512020/tables/MstEvent.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__') e
LEFT JOIN read_csv('マスタデータ/リリース/202512020/tables/MstSeries.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__') s
  ON e.mst_series_id = s.id;
```

#### イベントと多言語名称の結合

```sql
SELECT
  e.id,
  e.mst_series_id,
  i.language,
  i.name
FROM read_csv('マスタデータ/リリース/202512020/tables/MstEvent.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__') e
LEFT JOIN read_csv('マスタデータ/リリース/202512020/tables/MstEventI18n.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__') i
  ON e.id = i.mst_event_id
WHERE i.language = 'ja';
```

#### 降臨バトルと報酬の詳細

```sql
SELECT
  ab.id as battle_id,
  r.resource_type,
  r.resource_id,
  r.resource_amount
FROM read_csv('マスタデータ/リリース/202512020/tables/MstAdventBattle.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__') ab
JOIN read_csv('マスタデータ/リリース/202512020/tables/MstAdventBattleRewardGroup.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__') rg
  ON ab.id = rg.mst_advent_battle_id
JOIN read_csv('マスタデータ/リリース/202512020/tables/MstAdventBattleReward.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__') r
  ON rg.id = r.mst_advent_battle_reward_group_id;
```

---

### 3. 集計・統計

#### リソースタイプ別の報酬数

```sql
SELECT
  resource_type,
  COUNT(*) as count,
  SUM(resource_amount) as total_amount
FROM read_csv('マスタデータ/リリース/202512020/tables/MstAdventBattleReward.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
GROUP BY resource_type
ORDER BY count DESC;
```

#### カテゴリ別データ行数

```sql
SELECT
  CASE
    WHEN table_name LIKE 'Mst%I18n' THEN 'I18n'
    WHEN table_name LIKE 'Mst%' THEN 'Mst'
    WHEN table_name LIKE 'Opr%' THEN 'Opr'
    ELSE 'Other'
  END as category,
  COUNT(*) as table_count
FROM (
  SELECT REPLACE(file, '.csv', '') as table_name
  FROM glob('マスタデータ/リリース/202512020/tables/*.csv')
)
GROUP BY category;
```

#### レアリティ別ユニット数

```sql
SELECT
  rarity,
  COUNT(*) as unit_count
FROM read_csv('マスタデータ/リリース/202512020/tables/MstUnit.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
GROUP BY rarity
ORDER BY
  CASE rarity
    WHEN 'UR' THEN 1
    WHEN 'SSR' THEN 2
    WHEN 'SR' THEN 3
    WHEN 'R' THEN 4
    ELSE 5
  END;
```

#### 属性別ユニット分布

```sql
SELECT
  color as attribute,
  rarity,
  COUNT(*) as count
FROM read_csv('マスタデータ/リリース/202512020/tables/MstUnit.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
GROUP BY color, rarity
ORDER BY rarity, color;
```

---

### 4. 高度なクエリ

#### ウィンドウ関数: テーブルごとの最新データ

```sql
SELECT *
FROM (
  SELECT *,
    ROW_NUMBER() OVER (PARTITION BY id ORDER BY release_key DESC) as rn
  FROM read_csv('マスタデータ/リリース/202512020/tables/MstEvent.csv',
    AUTO_DETECT=TRUE, nullstr='__NULL__')
)
WHERE rn = 1;
```

#### UNION: 複数テーブルの統合

```sql
SELECT 'MstEvent' as table_name, COUNT(*) as count
FROM read_csv('マスタデータ/リリース/202512020/tables/MstEvent.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
UNION ALL
SELECT 'MstStage', COUNT(*)
FROM read_csv('マスタデータ/リリース/202512020/tables/MstStage.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
UNION ALL
SELECT 'MstUnit', COUNT(*)
FROM read_csv('マスタデータ/リリース/202512020/tables/MstUnit.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__');
```

#### サブクエリ: 報酬総額トップ10のバトル

```sql
SELECT
  battle_id,
  total_rewards
FROM (
  SELECT
    mst_advent_battle_id as battle_id,
    SUM(resource_amount) as total_rewards
  FROM read_csv('マスタデータ/リリース/202512020/tables/MstAdventBattleReward.csv',
    AUTO_DETECT=TRUE, nullstr='__NULL__')
  WHERE resource_type = 'FreeDiamond'
  GROUP BY mst_advent_battle_id
)
ORDER BY total_rewards DESC
LIMIT 10;
```

#### CASE文: カテゴリ分類

```sql
SELECT
  id,
  CASE
    WHEN id LIKE 'event_%' THEN 'Event'
    WHEN id LIKE 'quest_%' THEN 'Quest'
    WHEN id LIKE 'mission_%' THEN 'Mission'
    ELSE 'Other'
  END as category
FROM read_csv('マスタデータ/リリース/202512020/tables/MstStage.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__');
```

---

### 5. 便利なパターン

#### glob()で全テーブルを一括処理

```sql
-- 全テーブルのファイルパスを取得
SELECT * FROM glob('マスタデータ/リリース/202512020/tables/*.csv');

-- 全テーブルの行数を一括カウント
SELECT
  REPLACE(REPLACE(file, 'マスタデータ/リリース/202512020/tables/', ''), '.csv', '') as table_name,
  COUNT(*) as row_count
FROM (
  SELECT file, read_csv(file, AUTO_DETECT=TRUE, nullstr='__NULL__') as data
  FROM glob('マスタデータ/リリース/202512020/tables/*.csv')
)
GROUP BY file;
```

#### テーブル構造の確認

```sql
-- カラム一覧の取得
DESCRIBE SELECT * FROM read_csv('マスタデータ/リリース/202512020/tables/MstUnit.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__');

-- 最初の5行でサンプル確認
SELECT * FROM read_csv('マスタデータ/リリース/202512020/tables/MstUnit.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__')
LIMIT 5;
```

#### NULL値の確認

```sql
SELECT
  COUNT(*) as total,
  COUNT(asset_key) as asset_key_count,
  COUNT(*) - COUNT(asset_key) as asset_key_nulls
FROM read_csv('マスタデータ/リリース/202512020/tables/MstEvent.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__');
```

---

## Tips

### CSV読み込みオプション

- `AUTO_DETECT=TRUE`: カラム型を自動検出
- `nullstr='__NULL__'`: GLOW マスタデータのNULL値表現
- `header=TRUE`: ヘッダー行の有無（デフォルトtrue）

### エラー対処

```sql
-- TRY_CAST で型変換エラーを回避
SELECT
  id,
  TRY_CAST(start_at AS TIMESTAMP) as start_timestamp
FROM read_csv('マスタデータ/リリース/202512020/tables/MstEvent.csv',
  AUTO_DETECT=TRUE, nullstr='__NULL__');
```

### 出力形式の変更

```sql
-- モード変更
.mode csv         -- CSV形式
.mode json        -- JSON形式
.mode markdown    -- Markdown形式
.mode table       -- テーブル形式

-- ヘッダーの表示/非表示
.headers on       -- ヘッダー表示
.headers off      -- ヘッダー非表示
```

---

## 参考リンク

- [DuckDB公式ドキュメント](https://duckdb.org/docs/)
- [DuckDB SQL関数リファレンス](https://duckdb.org/docs/sql/functions/overview)
- [masterdata-explorer スキル](../.claude/skills/masterdata-explorer/references/duckdb-query-examples.md)
