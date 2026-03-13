# コンテンツ別絞り込みガイド

## 目次

1. [基本的なDuckDB起動方法](#1-基本的なduckdb起動方法)
2. [メインクエスト（Normal/Hard/VeryHard）](#2-メインクエスト)
3. [限界チャレンジ（VD）](#3-限界チャレンジvd)
4. [イベントクエスト](#4-イベントクエスト)
5. [降臨バトル](#5-降臨バトル)
6. [降臨バトルボスパラメータ（AdventBattleBoss）](#6-降臨バトルボスパラメータadventbattleboss)
7. [ランクマッチ（PvP）](#7-ランクマッチpvp)
8. [横断分析クエリ](#8-横断分析クエリ)

---

## 1. 基本的なDuckDB起動方法

```bash
# DuckDB起動（glow-brainルートから実行）
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc

# 対話形式で複数クエリを実行する場合
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc -c "SELECT ..."

# カラム名確認（クエリ前に必ず実行）
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_in_games
```

**CSVパス**: `projects/glow-masterdata/{TableName}.csv`

**DuckDB共通オプション**: `AUTO_DETECT=TRUE, nullstr='__NULL__'`

---

## 2. メインクエスト

### 2-1. Normal難易度

**絞り込み条件**: `MstInGame.id LIKE 'normal_%'`

```sql
-- MstInGame（Normalステージ一覧）
SELECT id, normal_enemy_hp_coef, normal_enemy_attack_coef, boss_mst_enemy_stage_parameter_id
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'normal_%' AND ENABLE = 'e'
ORDER BY id;

-- シーケンス行数の分布
SELECT
    sequence_set_id,
    COUNT(*) as row_count,
    COUNT(CASE WHEN action_type = 'SummonEnemy' THEN 1 END) as summon_rows
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id LIKE 'normal_%' AND ENABLE = 'e'
GROUP BY sequence_set_id
ORDER BY row_count DESC;

-- 敵パラメータ一覧（Normal用）
SELECT id, character_unit_kind, role_type, color, hp, move_speed, attack_power
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE '%_normal_%' AND ENABLE = 'e'
ORDER BY hp DESC;
```

### 2-2. Hard難易度

```sql
-- Hard ステージの倍率分布
SELECT
    id,
    normal_enemy_hp_coef,
    normal_enemy_attack_coef,
    boss_mst_enemy_stage_parameter_id
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'hard_%' AND ENABLE = 'e'
ORDER BY normal_enemy_hp_coef DESC;
```

### 2-3. MstQuestで絞り込む（より精密な方法）

```sql
-- MstQuestが存在する場合はquest_typeとdifficultyで絞り込み可能
-- ※MstQuestテーブルはDBスキーマを確認してカラム名を事前確認すること
SELECT q.id, q.difficulty, ig.*
FROM read_csv('projects/glow-masterdata/MstQuest.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') q
JOIN read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ig
    ON q.mst_in_game_id = ig.id
WHERE q.difficulty = 'Normal' AND q.ENABLE = 'e' AND ig.ENABLE = 'e';
```

---

## 3. 限界チャレンジ（VD）

### 3-1. VD Normalブロック

**絞り込み条件**: `MstInGame.id LIKE 'vd_%normal%'`

```sql
-- VD Normalブロック一覧
SELECT id, normal_enemy_hp_coef, normal_enemy_attack_coef, boss_mst_enemy_stage_parameter_id
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'vd_%normal%' AND ENABLE = 'e'
ORDER BY id;

-- VD専用敵パラメータ一覧
SELECT id, mst_enemy_character_id, character_unit_kind, color, hp, move_speed, attack_power
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE '%_vd_%' AND ENABLE = 'e'
ORDER BY character_unit_kind, hp DESC;

-- VDシーケンスの構造分析
SELECT
    sequence_set_id,
    COUNT(*) as total_rows,
    COUNT(CASE WHEN action_type = 'SummonEnemy' THEN 1 END) as summon_count,
    COUNT(CASE WHEN action_type = 'SwitchSequenceGroup' THEN 1 END) as group_changes,
    COUNT(DISTINCT sequence_group_id) as group_count
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id LIKE 'vd_%normal%' AND ENABLE = 'e'
GROUP BY sequence_set_id;
```

### 3-2. VD Bossブロック

```sql
-- VD Bossブロック一覧
SELECT id, boss_mst_enemy_stage_parameter_id, boss_enemy_hp_coef, boss_enemy_attack_coef
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'vd_%boss%' AND ENABLE = 'e'
ORDER BY id;
```

### 3-3. VDの最終HP計算

```sql
-- VDシーケンス × 敵パラメータ × MstInGame倍率でFinal HP計算
SELECT
    s.sequence_set_id,
    s.sequence_element_id,
    p.id as enemy_id,
    p.character_unit_kind,
    p.hp as base_hp,
    ig.normal_enemy_hp_coef as stage_coef,
    CAST(s.enemy_hp_coef AS FLOAT) as seq_coef,
    ROUND(p.hp * ig.normal_enemy_hp_coef * CAST(s.enemy_hp_coef AS FLOAT), 0) as final_hp
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') s
JOIN read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') p
    ON s.action_value = p.id
JOIN read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ig
    ON s.sequence_set_id = ig.id
WHERE s.action_type = 'SummonEnemy'
    AND s.sequence_set_id LIKE 'vd_%'
    AND s.ENABLE = 'e' AND p.ENABLE = 'e' AND ig.ENABLE = 'e'
ORDER BY s.sequence_set_id, CAST(s.sequence_element_id AS INTEGER);
```

---

## 4. イベントクエスト

### 4-1. チャレンジクエスト

```sql
-- イベントチャレンジ一覧
SELECT id, normal_enemy_hp_coef, normal_enemy_attack_coef
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'event_%challenge%' AND ENABLE = 'e'
ORDER BY id;
```

### 4-2. キャラゲットクエスト

```sql
-- キャラゲットクエスト
SELECT id, normal_enemy_hp_coef, boss_mst_enemy_stage_parameter_id
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'event_%charaget%' AND ENABLE = 'e'
ORDER BY id;
```

### 4-3. サベージバトル

```sql
SELECT id, normal_enemy_hp_coef, normal_enemy_attack_coef, boss_enemy_hp_coef
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'event_%savage%' AND ENABLE = 'e'
ORDER BY id;
```

### 4-4. キャラ別イベントデータ

```sql
-- 特定キャラ（例: kai）のイベントデータ
SELECT id, normal_enemy_hp_coef, release_key
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'event_kai%' AND ENABLE = 'e'
ORDER BY release_key, id;
```

---

## 5. 降臨バトル

> データ内部IDのプレフィックスは `raid_`。ゲーム内の呼称は「降臨バトル」。

```sql
-- 降臨バトル一覧
SELECT id, normal_enemy_hp_coef, normal_enemy_attack_coef, mst_enemy_outpost_id
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'raid_%' AND ENABLE = 'e'
ORDER BY id;

-- 降臨バトルシーケンスの全ウェーブ構造
SELECT
    sequence_set_id,
    sequence_group_id,
    sequence_element_id,
    condition_type,
    condition_value,
    action_type,
    action_value,
    summon_count,
    CAST(enemy_hp_coef AS FLOAT) as hp_coef,
    aura_type,
    defeated_score
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id LIKE 'raid_%' AND ENABLE = 'e'
ORDER BY sequence_set_id, sequence_group_id, CAST(sequence_element_id AS VARCHAR);
```

---

## 6. 降臨バトルボスパラメータ（AdventBattleBoss）

> 降臨バトルで使用するボス敵パラメータを `character_unit_kind = 'AdventBattleBoss'` で絞り込む。
> Section 5 は MstInGame 側（ステージ）の絞り込み、Section 6 は MstEnemyStageParameter 側（敵パラメータ）の絞り込み。

**絞り込み条件**: `character_unit_kind = 'AdventBattleBoss'`

```sql
-- 降臨バトルボスパラメータ一覧
SELECT id, mst_enemy_character_id, color, hp, move_speed, attack_power, mst_unit_ability_id1
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE character_unit_kind = 'AdventBattleBoss' AND ENABLE = 'e'
ORDER BY hp DESC;

-- 降臨バトルのシーケンスデータ（aura_typeがAdventBossX）
SELECT
    sequence_set_id,
    sequence_element_id,
    action_value as enemy_id,
    summon_count,
    CAST(enemy_hp_coef AS FLOAT) as hp_coef,
    aura_type,
    defeated_score
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE aura_type LIKE 'AdventBoss%' AND ENABLE = 'e'
ORDER BY sequence_set_id, CAST(sequence_element_id AS VARCHAR);
```

---

## 7. ランクマッチ（PvP）

> データ内部IDのプレフィックスは `pvp_`。ゲーム内の呼称は「ランクマッチ」。

```sql
-- ランクマッチ（PvP）ステージ一覧
SELECT id, mst_page_id, mst_enemy_outpost_id, release_key
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'pvp_%' OR id = 'default_pvp' AND ENABLE = 'e'
ORDER BY release_key, id;
```

---

## 8. 横断分析クエリ

### 8-1. コンテンツ別レコード数

```sql
SELECT
    CASE
        WHEN id LIKE 'normal_%' THEN 'normal'
        WHEN id LIKE 'hard_%' THEN 'hard'
        WHEN id LIKE 'veryhard_%' THEN 'veryhard'
        WHEN id LIKE 'vd_%' THEN 'vd'
        WHEN id LIKE 'raid_%' THEN 'raid'
        WHEN id LIKE 'event_%' THEN 'event'
        WHEN id LIKE 'pvp%' THEN 'pvp'
        WHEN id LIKE 'tutorial%' THEN 'tutorial'
        ELSE 'other'
    END as content_type,
    COUNT(*) as count
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
GROUP BY content_type
ORDER BY count DESC;
```

### 8-2. コンテンツ別HP倍率比較

```sql
SELECT
    CASE
        WHEN id LIKE 'normal_%' THEN 'normal'
        WHEN id LIKE 'hard_%' THEN 'hard'
        WHEN id LIKE 'veryhard_%' THEN 'veryhard'
        WHEN id LIKE 'vd_%' THEN 'vd'
        ELSE 'other'
    END as content_type,
    ROUND(AVG(normal_enemy_hp_coef), 2) as avg_hp_coef,
    ROUND(MIN(normal_enemy_hp_coef), 2) as min_hp_coef,
    ROUND(MAX(normal_enemy_hp_coef), 2) as max_hp_coef,
    ROUND(AVG(normal_enemy_attack_coef), 2) as avg_atk_coef
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e' AND id NOT LIKE 'pvp%' AND id NOT LIKE 'raid%'
GROUP BY content_type
ORDER BY avg_hp_coef DESC;
```

### 8-3. コマエフェクト使用状況

```sql
SELECT
    koma1_effect_type,
    COUNT(*) as count,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 1) as pct
FROM read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
GROUP BY koma1_effect_type
ORDER BY count DESC;
```

### 8-4. 敵character_unit_kind別統計

```sql
SELECT
    character_unit_kind,
    COUNT(*) as count,
    ROUND(AVG(hp), 0) as avg_hp,
    ROUND(AVG(move_speed), 1) as avg_speed,
    ROUND(AVG(attack_power), 0) as avg_atk
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
GROUP BY character_unit_kind
ORDER BY avg_hp DESC;
```

### 8-5. アビリティ使用率（MstEnemyStageParameter）

```sql
SELECT
    CASE WHEN mst_unit_ability_id1 = '' OR mst_unit_ability_id1 IS NULL THEN 'なし'
         ELSE mst_unit_ability_id1
    END as ability,
    COUNT(*) as count
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
GROUP BY ability
ORDER BY count DESC
LIMIT 20;
```

### 8-6. 変身パターン分析

```sql
-- 変身あり敵のパラメータ
SELECT
    id,
    character_unit_kind,
    hp,
    transformationConditionType,
    transformationConditionValue,
    mstTransformationEnemyStageParameterId as transform_to
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE transformationConditionType != 'None'
    AND transformationConditionType != ''
    AND ENABLE = 'e'
ORDER BY id;
```
