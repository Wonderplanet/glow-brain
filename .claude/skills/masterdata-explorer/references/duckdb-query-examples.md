# DuckDB Query Examples for GLOW Masterdata

このドキュメントでは、GLOWマスタデータに対する実践的なDuckDBクエリパターンを紹介します。

## クイックスタート

```bash
# glow-brainルートディレクトリに移動
cd /Users/junki.mizutani/Documents/workspace/glow/glow-brain

# DuckDB起動（初期化ファイル込み）
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc

# クエリを実行
D SELECT * FROM read_csv('projects/glow-masterdata/MstUnit') WHERE ENABLE = 'e' LIMIT 10;

# 終了
D .quit
```

---

## 基本クエリ（5パターン）

### 1. 有効レコードの一覧

```sql
SELECT *
FROM read_csv('projects/glow-masterdata/MstUnit.csv'WHERE ENABLE = 'e'
WHERE ENABLE = 'e'
LIMIT 10;
```

**用途**: 有効なマスタデータのみを抽出

### 2. 特定IDの完全一致検索

```sql
SELECT * FROM read_csv('projects/glow-masterdata/MstUnit.csv'WHERE ENABLE = 'e'
WHERE id = 'chara_dan_00001';
```

**用途**: 特定のマスタデータを完全一致で検索

### 3. 部分一致検索（LIKE）

```sql
-- danシリーズのキャラクターを検索
SELECT * FROM read_csv('projects/glow-masterdata/MstUnit') WHERE id LIKE 'chara_dan%';

-- 複数パターン
SELECT * FROM read_csv('projects/glow-masterdata/MstUnit') WHERE id LIKE 'chara_dan%' OR id LIKE 'chara_spy%';
```

**用途**: IDやasset_keyの部分一致検索（シリーズ別抽出など）

### 4. レア度別カウント

```sql
-- シンプルなカウント
SELECT COUNT(*) FROM read_csv('projects/glow-masterdata/MstUnit') WHERE rarity = 'UR';

-- レア度ごとの集計
SELECT rarity, COUNT(*) as count
FROM read_csv('projects/glow-masterdata/MstUnit')
GROUP BY rarity
ORDER BY count DESC;
```

**用途**: データ量の把握、レア度分布の確認

### 5. 日付範囲検索

```sql
SELECT *
FROM read_csv('projects/glow-masterdata/MstEvent')
WHERE ENABLE = 'e'
  AND start_at >= '2025-09-01 00:00:00'
  AND end_at <= '2025-12-31 23:59:59';
```

**用途**: 特定期間のイベント抽出

---

## JOIN分析（5パターン）

### 6. イベントとシリーズの紐付け

```sql
SELECT
  e.id as event_id,
  e.start_at,
  e.end_at,
  s.id as series_id,
  s.asset_key as series_name
FROM read_csv('projects/glow-masterdata/MstEvent') e
JOIN csv('MstSeries') s ON e.mst_series_id = s.id
WHERE e.ENABLE = 'e' AND s.ENABLE = 'e';
```

**用途**: イベントがどのシリーズに属するかを確認

### 7. ユニットとシリーズの集計

```sql
SELECT
  s.id as series_id,
  s.asset_key,
  COUNT(u.id) as unit_count,
  COUNT(CASE WHEN u.rarity = 'UR' THEN 1 END) as ur_count,
  COUNT(CASE WHEN u.rarity = 'SSR' THEN 1 END) as ssr_count
FROM read_csv('projects/glow-masterdata/MstSeries') s
LEFT JOIN csv('MstUnit') u
  ON s.id = u.mst_series_id AND u.ENABLE = 'e'
WHERE s.ENABLE = 'e'
GROUP BY s.id, s.asset_key
ORDER BY unit_count DESC;
```

**用途**: シリーズ別のキャラクター数とレア度分布を確認

### 8. ステージ報酬の詳細展開

```sql
SELECT
  r.mst_stage_id,
  r.reward_category,
  r.resource_type,
  r.resource_id,
  r.resource_amount,
  i.asset_key as item_asset_key
FROM read_csv('projects/glow-masterdata/MstStageReward') r
LEFT JOIN csv('MstItem') i
  ON r.resource_id = i.id AND r.resource_type = 'Item'
WHERE r.ENABLE = 'e'
  AND r.mst_stage_id = 'normal_spy_00001';
```

**用途**: ステージ報酬でどのアイテムが得られるかを確認

### 9. ユニットとアビリティの結合

```sql
SELECT
  u.id as unit_id,
  u.rarity,
  u.mst_unit_ability_id1,
  a1.mst_ability_id as ability_id_1,
  ab1.ability_type as ability_type_1
FROM read_csv('projects/glow-masterdata/MstUnit') u
LEFT JOIN csv('MstUnitAbility') a1
  ON u.mst_unit_ability_id1 = a1.id
LEFT JOIN csv('MstAbility') ab1
  ON a1.mst_ability_id = ab1.id
WHERE u.ENABLE = 'e'
  AND u.rarity = 'UR'
LIMIT 10;
```

**用途**: ユニットが持つアビリティを詳細に確認

### 10. アートワークとフラグメントの関連

```sql
SELECT
  a.id as artwork_id,
  a.asset_key,
  f.id as fragment_id,
  f.rarity as fragment_rarity,
  f.drop_percentage
FROM read_csv('projects/glow-masterdata/MstArtwork') a
LEFT JOIN csv('MstArtworkFragment') f
  ON a.id = f.mst_artwork_id
WHERE a.ENABLE = 'e' AND f.ENABLE = 'e'
ORDER BY a.asset_key, f.rarity;
```

**用途**: アートワークに必要なフラグメントとドロップ率を確認

---

## 集計・統計（5パターン）

### 11. テーブルごとのレコード数

```sql
SELECT 'MstUnit' as table_name,
       COUNT(*) as total,
       COUNT(CASE WHEN ENABLE = 'e' THEN 1 END) as enabled
FROM read_csv('projects/glow-masterdata/MstUnit')
UNION ALL
SELECT 'MstEvent', COUNT(*), COUNT(CASE WHEN ENABLE = 'e' THEN 1 END)
FROM read_csv('projects/glow-masterdata/MstEvent')
UNION ALL
SELECT 'MstStage', COUNT(*), COUNT(CASE WHEN ENABLE = 'e' THEN 1 END)
FROM read_csv('projects/glow-masterdata/MstStage');
```

**用途**: 各テーブルのデータ量を一覧表示

### 12. シリーズ別キャラクター数

```sql
SELECT
  mst_series_id,
  COUNT(*) as unit_count,
  COUNT(DISTINCT rarity) as rarity_variety
FROM read_csv('projects/glow-masterdata/MstUnit')
GROUP BY mst_series_id
ORDER BY unit_count DESC;
```

**用途**: どのシリーズに何体のキャラクターがいるかを確認

### 13. レア度別分布（パーセンテージ付き）

```sql
SELECT
  rarity,
  COUNT(*) as count,
  ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage
FROM read_csv('projects/glow-masterdata/MstUnit')
GROUP BY rarity
ORDER BY count DESC;
```

**用途**: レア度ごとの分布をパーセンテージで確認

### 14. NULL値の集計

```sql
SELECT
  'mst_unit_ability_id1' as column_name,
  COUNT(*) as total,
  COUNT(mst_unit_ability_id1) as non_null,
  COUNT(*) - COUNT(mst_unit_ability_id1) as null_count
FROM read_csv('projects/glow-masterdata/MstUnit');
```

**用途**: NULL値の有無を確認し、データの完全性をチェック

### 15. リリースバージョン別イベント数

```sql
SELECT
  release_key,
  COUNT(*) as event_count,
  MIN(start_at) as earliest_start,
  MAX(end_at) as latest_end
FROM read_csv('projects/glow-masterdata/MstEvent')
GROUP BY release_key
ORDER BY release_key DESC;
```

**用途**: バージョンごとのイベント数と期間を確認

---

## 高度なクエリ（5パターン）

### 16. ウィンドウ関数（RANK, ROW_NUMBER）

```sql
SELECT
  id,
  rarity,
  max_hp,
  max_attack_power,
  RANK() OVER (PARTITION BY rarity ORDER BY max_attack_power DESC) as attack_rank_in_rarity,
  ROW_NUMBER() OVER (ORDER BY max_attack_power DESC) as overall_attack_rank
FROM read_csv('projects/glow-masterdata/MstUnit')
ORDER BY max_attack_power DESC
LIMIT 20;
```

**用途**: レア度内でのランキング、全体でのランキングを同時に表示

### 17. 複数テーブルのUNION

```sql
SELECT 'Unit' as resource_type, id, asset_key, rarity
FROM read_csv('projects/glow-masterdata/MstUnit')
UNION ALL
SELECT 'Item', id, asset_key, rarity
FROM read_csv('projects/glow-masterdata/MstItem');
```

**用途**: 異なるテーブルのデータを統合して一覧表示

### 18. サブクエリでのフィルタリング

```sql
SELECT *
FROM read_csv('projects/glow-masterdata/MstUnit')
WHERE mst_series_id IN (
  SELECT id
  FROM read_csv('projects/glow-masterdata/MstSeries')
  WHERE asset_key IN ('dan', 'spy')
);
```

**用途**: 特定条件を満たすシリーズに属するユニットのみを抽出

### 19. CASE文での条件分岐

```sql
SELECT
  id,
  rarity,
  max_attack_power,
  CASE
    WHEN max_attack_power >= 40000 THEN 'S級'
    WHEN max_attack_power >= 20000 THEN 'A級'
    WHEN max_attack_power >= 10000 THEN 'B級'
    ELSE 'C級'
  END as attack_tier
FROM read_csv('projects/glow-masterdata/MstUnit')
ORDER BY max_attack_power DESC;
```

**用途**: 数値データをカテゴリ分けして分析

### 20. 正規表現を使った検索

```sql
SELECT
  id,
  asset_key,
  rarity
FROM read_csv('projects/glow-masterdata/MstUnit')
WHERE regexp_matches(id, 'chara_(dan|spy|jig)_[0-9]{5}')
ORDER BY id;
```

**用途**: 複雑なパターンマッチングでデータを抽出

---

## よく使うクエリのショートカット

### 全シリーズのユニット数を確認

```sql
SELECT
  s.asset_key as series,
  COUNT(u.id) as units
FROM read_csv('projects/glow-masterdata/MstSeries') s
LEFT JOIN csv('MstUnit') u
  ON s.id = u.mst_series_id AND u.ENABLE = 'e'
WHERE s.ENABLE = 'e'
GROUP BY s.asset_key
ORDER BY units DESC;
```

### 報酬の整合性チェック

```sql
SELECT
  r.mst_stage_id,
  r.resource_type,
  r.resource_id,
  CASE WHEN i.id IS NULL THEN '❌ NOT FOUND' ELSE '✅ OK' END as status
FROM read_csv('projects/glow-masterdata/MstStageReward') r
LEFT JOIN csv('MstItem') i
  ON r.resource_id = i.id AND r.resource_type = 'Item'
WHERE r.ENABLE = 'e'
GROUP BY r.mst_stage_id, r.resource_type, r.resource_id, i.id
HAVING status = '❌ NOT FOUND';
```

### 現在開催中のイベント

```sql
SELECT *
FROM read_csv('projects/glow-masterdata/MstEvent')
WHERE start_at <= CURRENT_TIMESTAMP
  AND end_at >= CURRENT_TIMESTAMP;
```

---

## DuckDBの便利機能

### 出力形式の変更

```sql
-- テーブル形式で見やすく表示
.mode table
SELECT * FROM read_csv('projects/glow-masterdata/MstUnit') WHERE rarity = 'UR' LIMIT 5;

-- Markdown形式（ドキュメント用）
.mode markdown
SELECT rarity, COUNT(*) as count FROM read_csv('projects/glow-masterdata/MstUnit') GROUP BY rarity;

-- CSV形式（デフォルト）
.mode csv
```

### 結果をファイルに出力

```sql
-- 出力先を指定
.output results.csv

-- クエリ実行
SELECT * FROM read_csv('projects/glow-masterdata/MstUnit') WHERE rarity = 'UR';

-- 標準出力に戻す
.output
```

### クエリ実行時間を計測

```sql
.timer on
SELECT COUNT(*) FROM read_csv('projects/glow-masterdata/MstUnit');
-- Run time: real 0.012s user 0.008s sys 0.003s
```

---

## パフォーマンスのヒント

1. **必要なカラムのみを取得**: `SELECT *` ではなく `SELECT id, name` を使用
2. **WHERE句でフィルタリング**: JOINの前にWHERE句でレコード数を減らす
3. **LIMIT句の活用**: 大量データの確認時は必ずLIMIT句を使用
4. **マクロの活用**: 繰り返し使うパターンはマクロ化

---

## トラブルシューティング

### エラー: No files found

```sql
-- エラー例
SELECT * FROM read_csv('projects/glow-masterdata/MstUnit');
-- IO Error: No files found that match the pattern

-- 原因: 相対パスが正しくない
-- 解決: glow-brainルートディレクトリから起動しているか確認
cd /Users/junki.mizutani/Documents/workspace/glow/glow-brain
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc
```

### マクロが認識されない

```sql
-- エラー例
SELECT * FROM read_csv('projects/glow-masterdata/MstUnit');
-- Error: csv is not recognized

-- 原因: 初期化ファイルを読み込んでいない
-- 解決: -initオプション付きで起動
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc
```

### テーブル名がわからない

```sql
-- CSVファイル一覧を確認
SELECT * FROM glob('projects/glow-masterdata/*.csv');
```

---

## 参考リンク

- [DuckDB公式ドキュメント](https://duckdb.org/docs/)
- [DuckDB SQL Reference](https://duckdb.org/docs/sql/introduction)
- GLOW マスタデータスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
