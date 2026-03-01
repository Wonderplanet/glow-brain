# DuckDBクエリ集（Step 2〜4）

Step 2〜4で使用するDuckDBクエリ。
クエリ内の `{generated}` は実際の生成ディレクトリパスに置換して使用する。

例: `{generated}` → `domain/tasks/masterdata-entry/masterdata-ingame-creator/20260301_131508_dungeon_spy_normal_block/generated`

DuckDB実行方法:
```bash
duckdb -c "<SQL>"
```

---

## Step 3-1: 敵パラメータ確認

```sql
-- 全エネミーパラメータ一覧
SELECT
  id,
  mst_enemy_character_id,
  character_unit_kind,
  role_type,
  hp,
  attack_power,
  move_speed,
  well_distance,
  drop_battle_point
FROM read_csv('{generated}/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE)
ORDER BY character_unit_kind, role_type;
```

```sql
-- attack_power が WARNING閾値（3,800）を超えているか
SELECT id, character_unit_kind, role_type, attack_power
FROM read_csv('{generated}/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE)
WHERE CAST(attack_power AS INTEGER) > 3800;
```

```sql
-- move_speed が通常範囲外（5〜100）かチェック
SELECT id, character_unit_kind, move_speed
FROM read_csv('{generated}/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE)
WHERE CAST(move_speed AS INTEGER) < 5 OR CAST(move_speed AS INTEGER) > 100;
```

```sql
-- well_distance が通常範囲外（0.05〜2.0）かチェック
SELECT id, character_unit_kind, well_distance
FROM read_csv('{generated}/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE)
WHERE CAST(well_distance AS DOUBLE) < 0.05 OR CAST(well_distance AS DOUBLE) > 2.0;
```

---

## Step 3-2: コマ配置の整合性

> **MstKomaLineのカラム構造**: `row`（行番号）、`height`、`koma1_width〜koma4_width`（コマごとの幅）
> 行ごとにkoma1〜koma4のwidthが横並びで格納される。NULL = そのコマ未使用。

```sql
-- 各行のコマ幅合計（1.0 = OK）
SELECT
  row,
  ROUND(COALESCE(CAST(koma1_width AS DOUBLE), 0)
      + COALESCE(CAST(koma2_width AS DOUBLE), 0)
      + COALESCE(CAST(koma3_width AS DOUBLE), 0)
      + COALESCE(CAST(koma4_width AS DOUBLE), 0), 3) AS total_width
FROM read_csv('{generated}/MstKomaLine.csv', AUTO_DETECT=TRUE)
ORDER BY row;
```

```sql
-- height合計（参考情報）
-- 注意: height合計に固定制限はない。既存データでは 1.1（2行コマ最多）、1.65（3行コマ最多）など多様な値をとる
SELECT
  ROUND(SUM(CAST(height AS DOUBLE)), 2) AS total_height
FROM read_csv('{generated}/MstKomaLine.csv', AUTO_DETECT=TRUE);
```

```sql
-- コマ行数の確認（dungeon_boss=1行、dungeon_normal=3行）
SELECT COUNT(DISTINCT row) AS distinct_row_count
FROM read_csv('{generated}/MstKomaLine.csv', AUTO_DETECT=TRUE);
```

```sql
-- コマ一覧（構造確認用）
SELECT row, id, height, koma1_asset_key, koma1_width, koma2_asset_key, koma2_width, koma3_asset_key, koma3_width
FROM read_csv('{generated}/MstKomaLine.csv', AUTO_DETECT=TRUE)
ORDER BY row;
```

---

## Step 3-3: シーケンスの合理性

```sql
-- 召喚数の確認
SELECT COUNT(*) AS summon_count
FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE)
WHERE action_type = 'SummonEnemy';
```

```sql
-- action_type別行数
SELECT action_type, COUNT(*) AS count
FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE)
GROUP BY action_type
ORDER BY count DESC;
```

```sql
-- condition_type別行数
SELECT condition_type, COUNT(*) AS count
FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE)
GROUP BY condition_type
ORDER BY count DESC;
```

```sql
-- ElapsedTime条件の時系列確認（単調増加か）
WITH seq AS (
  SELECT
    sort_order,
    condition_value,
    LAG(CAST(condition_value AS DOUBLE)) OVER (ORDER BY CAST(sort_order AS INTEGER)) AS prev_condition_value
  FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE)
  WHERE condition_type = 'ElapsedTime'
)
SELECT
  sort_order,
  condition_value,
  prev_condition_value,
  CAST(condition_value AS DOUBLE) < prev_condition_value AS is_time_regression
FROM seq
WHERE is_time_regression = true;
```

---

## Step 3-4: ステージ種別固有ルール

```sql
-- MstEnemyOutpost の hp と is_damage_invalidation 確認
SELECT id, hp, is_damage_invalidation
FROM read_csv('{generated}/MstEnemyOutpost.csv', AUTO_DETECT=TRUE);
```

```sql
-- MstInGameSpecialRule（存在すれば）
SELECT rule_type, rule_value, content_type
FROM read_csv('{generated}/MstInGameSpecialRule.csv', AUTO_DETECT=TRUE);
```

---

## Step 3-5: ボス設定確認

```sql
-- MstInGameのボス設定確認
SELECT
  id,
  boss_mst_enemy_stage_parameter_id,
  boss_count,
  normal_enemy_hp_coef,
  normal_enemy_attack_coef,
  boss_enemy_hp_coef,
  boss_enemy_attack_coef
FROM read_csv('{generated}/MstInGame.csv', AUTO_DETECT=TRUE);
```

```sql
-- InitialSummon行の確認（ボスの初期召喚設定）
SELECT sort_order, action_type, action_value, condition_type, condition_value
FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE)
WHERE condition_type = 'InitialSummon';
```

```sql
-- ボスIDがシーケンスに含まれているか（JOIN確認）
WITH boss AS (
  SELECT boss_mst_enemy_stage_parameter_id AS boss_id
  FROM read_csv('{generated}/MstInGame.csv', AUTO_DETECT=TRUE)
  WHERE boss_mst_enemy_stage_parameter_id IS NOT NULL
    AND boss_mst_enemy_stage_parameter_id != ''
),
initial_summons AS (
  SELECT action_value
  FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE)
  WHERE condition_type = 'InitialSummon' AND action_type = 'SummonEnemy'
)
SELECT
  boss.boss_id,
  initial_summons.action_value IS NOT NULL AS found_in_sequence
FROM boss
LEFT JOIN initial_summons ON boss.boss_id = initial_summons.action_value;
```

---

## Step 4: バランス比較（既存データとの比較）

```sql
-- 既存データの同種ステージのパラメータ分布（Normal）
SELECT
  character_unit_kind,
  role_type,
  COUNT(*) AS count,
  MIN(hp) AS hp_min,
  ROUND(AVG(hp), 0) AS hp_avg,
  MAX(hp) AS hp_max,
  MIN(attack_power) AS atk_min,
  ROUND(AVG(attack_power), 0) AS atk_avg,
  MAX(attack_power) AS atk_max,
  MIN(move_speed) AS speed_min,
  ROUND(AVG(move_speed), 1) AS speed_avg,
  MAX(move_speed) AS speed_max
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE)
WHERE character_unit_kind = 'Normal'
GROUP BY character_unit_kind, role_type
ORDER BY role_type;
```

```sql
-- 既存データの同種ステージのパラメータ分布（Boss）
SELECT
  character_unit_kind,
  role_type,
  COUNT(*) AS count,
  MIN(hp) AS hp_min,
  ROUND(AVG(hp), 0) AS hp_avg,
  MAX(hp) AS hp_max,
  MIN(attack_power) AS atk_min,
  ROUND(AVG(attack_power), 0) AS atk_avg,
  MAX(attack_power) AS atk_max
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE)
WHERE character_unit_kind = 'Boss'
GROUP BY character_unit_kind, role_type
ORDER BY role_type;
```

```sql
-- 生成データのパラメータを既存データの平均と比較
SELECT
  gen.id AS gen_id,
  gen.character_unit_kind,
  gen.role_type,
  gen.hp AS gen_hp,
  ROUND(existing.hp_avg, 0) AS existing_hp_avg,
  ROUND(CAST(gen.hp AS DOUBLE) / existing.hp_avg, 2) AS hp_ratio,
  gen.attack_power AS gen_atk,
  ROUND(existing.atk_avg, 0) AS existing_atk_avg,
  ROUND(CAST(gen.attack_power AS DOUBLE) / existing.atk_avg, 2) AS atk_ratio
FROM read_csv('{generated}/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE) AS gen
LEFT JOIN (
  SELECT character_unit_kind, role_type,
         AVG(hp) AS hp_avg, AVG(attack_power) AS atk_avg
  FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE)
  GROUP BY character_unit_kind, role_type
) AS existing
  ON gen.character_unit_kind = existing.character_unit_kind
  AND gen.role_type = existing.role_type
ORDER BY gen.character_unit_kind, gen.role_type;
```

---

## Step 5: アセットキー確認

```sql
-- MstInGame のアセットキー確認
SELECT
  id,
  bgm_asset_key,
  boss_bgm_asset_key,
  loop_background_asset_key
FROM read_csv('{generated}/MstInGame.csv', AUTO_DETECT=TRUE);
```

```sql
-- MstEnemyOutpost のアセットキー確認
SELECT id, outpost_asset_key, artwork_asset_key
FROM read_csv('{generated}/MstEnemyOutpost.csv', AUTO_DETECT=TRUE);
```

```sql
-- MstKomaLine の koma_asset_key（空欄チェック）
SELECT line_number, sort_order, koma_asset_key,
       (koma_asset_key IS NULL OR koma_asset_key = '') AS is_empty
FROM read_csv('{generated}/MstKomaLine.csv', AUTO_DETECT=TRUE)
ORDER BY line_number, sort_order;
```

---

## Step 2: ID整合性（補助クエリ）

`verify_id_integrity.py` で自動チェックするが、手動確認が必要な場合に使用する。

```sql
-- MstInGame の FK値の確認
SELECT
  id,
  mst_auto_player_sequence_set_id,
  mst_page_id,
  mst_enemy_outpost_id,
  boss_mst_enemy_stage_parameter_id
FROM read_csv('{generated}/MstInGame.csv', AUTO_DETECT=TRUE);
```

```sql
-- sequence_set_id の全一致確認
SELECT DISTINCT sequence_set_id, COUNT(*) AS count
FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE)
GROUP BY sequence_set_id;
```

```sql
-- SummonEnemy の action_value と MstEnemyStageParameter.id の突合
SELECT
  seq.sort_order,
  seq.action_value,
  param.id IS NOT NULL AS found
FROM read_csv('{generated}/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE) AS seq
LEFT JOIN read_csv('{generated}/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE) AS param
  ON seq.action_value = param.id
WHERE seq.action_type = 'SummonEnemy';
```
