# DuckDBクエリ集（コピペ用）

`masterdata-ingame-enemy-explainer` スキルの各ステップで使用するDuckDBクエリです。

**DuckDB起動コマンド:**
```bash
duckdb -init .claude/skills/masterdata-explorer/.duckdbrc
```

---

## Step 1: キャラクター特定

### 1-A: IDで検索

```sql
SELECT
    ec.id,
    ec.mst_series_id,
    ec.asset_key,
    ec.is_phantomized,
    i18n.name,
    i18n.description
FROM read_csv('projects/glow-masterdata/MstEnemyCharacter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ec
LEFT JOIN read_csv('projects/glow-masterdata/MstEnemyCharacterI18n.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') i18n
    ON i18n.mst_enemy_character_id = ec.id AND i18n.language = 'ja'
WHERE ec.ENABLE = 'e'
    AND ec.id = '{target_id}';
```

### 1-B: 名前（日本語）で検索

```sql
SELECT
    ec.id,
    ec.mst_series_id,
    ec.asset_key,
    ec.is_phantomized,
    i18n.name,
    i18n.description
FROM read_csv('projects/glow-masterdata/MstEnemyCharacter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ec
LEFT JOIN read_csv('projects/glow-masterdata/MstEnemyCharacterI18n.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') i18n
    ON i18n.mst_enemy_character_id = ec.id AND i18n.language = 'ja'
WHERE ec.ENABLE = 'e'
    AND i18n.name = '{target_name}';
```

### 1-C: 部分一致で検索（名前があいまいな場合）

```sql
SELECT
    ec.id,
    ec.mst_series_id,
    i18n.name
FROM read_csv('projects/glow-masterdata/MstEnemyCharacter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ec
LEFT JOIN read_csv('projects/glow-masterdata/MstEnemyCharacterI18n.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') i18n
    ON i18n.mst_enemy_character_id = ec.id AND i18n.language = 'ja'
WHERE ec.ENABLE = 'e'
    AND (ec.id LIKE '%{keyword}%' OR i18n.name LIKE '%{keyword}%')
ORDER BY ec.id;
```

---

## Step 2: ステータスバリエーション収集

### 2-A: 全パラメータ一覧

```sql
SELECT
    id,
    mst_enemy_character_id,
    character_unit_kind,
    role_type,
    color,
    hp,
    attack_power,
    move_speed,
    well_distance,
    damage_knock_back_count,
    mst_unit_ability_id1,
    transformationConditionType,
    transformationConditionValue,
    mstTransformationEnemyStageParameterId
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
    AND mst_enemy_character_id = '{target_id}'
ORDER BY character_unit_kind, id;
```

### 2-B: ステータス統計（範囲・平均）

```sql
SELECT
    character_unit_kind,
    COUNT(*) as count,
    MIN(hp) as min_hp,
    MAX(hp) as max_hp,
    ROUND(AVG(hp), 0) as avg_hp,
    MIN(attack_power) as min_attack,
    MAX(attack_power) as max_attack,
    ROUND(AVG(attack_power), 0) as avg_attack,
    MIN(CAST(move_speed AS FLOAT)) as min_move_speed,
    MAX(CAST(move_speed AS FLOAT)) as max_move_speed,
    ROUND(AVG(CAST(move_speed AS FLOAT)), 2) as avg_move_speed
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
    AND mst_enemy_character_id = '{target_id}'
GROUP BY character_unit_kind
ORDER BY character_unit_kind;
```

### 2-C: 変身設定がある行のみ抽出

```sql
SELECT
    id,
    character_unit_kind,
    role_type,
    color,
    hp,
    transformationConditionType,
    transformationConditionValue,
    mstTransformationEnemyStageParameterId
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
    AND mst_enemy_character_id = '{target_id}'
    AND transformationConditionType IS NOT NULL
    AND transformationConditionType != 'None'
    AND transformationConditionType != ''
ORDER BY id;
```

---

## Step 3: 攻撃パターン収集

### 3-A: 対象キャラのパラメータIDに対応するMstAttackを取得

```sql
SELECT
    a.mst_unit_id,
    a.id as attack_id,
    a.unit_grade,
    a.attack_kind,
    a.killer_colors,
    a.killer_percentage,
    a.action_frames,
    a.attack_delay,
    a.next_attack_interval
FROM read_csv('projects/glow-masterdata/MstAttack.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') a
WHERE a.ENABLE = 'e'
    AND a.mst_unit_id IN ({param_id_list})
ORDER BY a.mst_unit_id, a.id;
```

> **{param_id_list}** は Step 2 で取得したパラメータIDのカンマ区切りリスト。
> VD専用パラメータ（`_vd_` を含む id）には MstAttack レコードが存在しないため結果が空になる場合がある。

### 3-B: 対応するMstAttackElementを取得

```sql
SELECT
    ae.mst_attack_id,
    ae.sort_order,
    ae.attack_type,
    ae.range_start_type,
    ae.range_start_parameter,
    ae.range_end_type,
    ae.range_end_parameter,
    ae.max_target_count,
    ae.target,
    ae.target_type,
    ae.target_colors,
    ae.target_roles,
    ae.damage_type,
    ae.hit_type,
    ae.power_parameter_type,
    ae.power_parameter,
    ae.effect_type,
    ae.effective_count,
    ae.effective_duration,
    ae.effect_parameter,
    ae.effect_value
FROM read_csv('projects/glow-masterdata/MstAttackElement.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ae
JOIN read_csv('projects/glow-masterdata/MstAttack.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') a
    ON ae.mst_attack_id = a.id
WHERE ae.ENABLE = 'e'
    AND a.ENABLE = 'e'
    AND a.mst_unit_id IN ({param_id_list})
ORDER BY ae.mst_attack_id, ae.sort_order;
```

---

## Step 4: インゲーム使用実績収集

### 4-A: 対象キャラを召喚しているシーケンスのインゲームIDを取得

```sql
-- Step 2 で取得したパラメータIDをカンマ区切りリストに置換
SELECT DISTINCT sequence_set_id
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
    AND action_type = 'SummonEnemy'
    AND action_value IN ({param_id_list})
ORDER BY sequence_set_id;
```

> **{param_id_list}** の形式例:
> `'e_kai_00301_general_1_Normal_Colorless', 'e_kai_00301_general_1_Boss_Green'`

### 4-B: インゲームID一覧のコンテンツ種別分類

```sql
SELECT
    id,
    CASE
        WHEN id LIKE 'vd_%normal%' THEN 'VD Normal'
        WHEN id LIKE 'vd_%boss%'   THEN 'VD Boss'
        WHEN id LIKE 'normal_%'    THEN 'メインクエスト Normal'
        WHEN id LIKE 'hard_%'      THEN 'メインクエスト Hard'
        WHEN id LIKE 'raid_%'      THEN '降臨バトル'
        WHEN id LIKE 'event_%'     THEN 'イベント'
        ELSE 'その他'
    END as content_type,
    mst_page_id,
    mst_auto_player_sequence_set_id
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
    AND id IN ({ingame_id_list})
ORDER BY content_type, id;
```

### 4-C: 使用ステージ一覧（パラメータ詳細つき）

```sql
SELECT
    seq.sequence_set_id as ingame_id,
    CASE
        WHEN seq.sequence_set_id LIKE 'vd_%normal%' THEN 'VD Normal'
        WHEN seq.sequence_set_id LIKE 'vd_%boss%'   THEN 'VD Boss'
        WHEN seq.sequence_set_id LIKE 'normal_%'    THEN 'メインクエスト Normal'
        WHEN seq.sequence_set_id LIKE 'hard_%'      THEN 'メインクエスト Hard'
        WHEN seq.sequence_set_id LIKE 'raid_%'      THEN '降臨バトル'
        WHEN seq.sequence_set_id LIKE 'event_%'     THEN 'イベント'
        ELSE 'その他'
    END as content_type,
    seq.action_value as param_id,
    param.character_unit_kind as kind,
    param.role_type as role,
    param.color
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') seq
JOIN read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') param
    ON seq.action_value = param.id
WHERE seq.ENABLE = 'e'
    AND param.ENABLE = 'e'
    AND seq.action_type = 'SummonEnemy'
    AND param.mst_enemy_character_id = '{target_id}'
ORDER BY content_type, ingame_id;
```

### 4-D: コンテンツ別件数サマリー

```sql
SELECT
    CASE
        WHEN sequence_set_id LIKE 'vd_%normal%' THEN 'VD Normal'
        WHEN sequence_set_id LIKE 'vd_%boss%'   THEN 'VD Boss'
        WHEN sequence_set_id LIKE 'normal_%'    THEN 'メインクエスト Normal'
        WHEN sequence_set_id LIKE 'hard_%'      THEN 'メインクエスト Hard'
        WHEN sequence_set_id LIKE 'raid_%'      THEN '降臨バトル'
        WHEN sequence_set_id LIKE 'event_%'     THEN 'イベント'
        ELSE 'その他'
    END as content_type,
    COUNT(DISTINCT sequence_set_id) as stage_count
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') seq
JOIN read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') param
    ON seq.action_value = param.id
WHERE seq.ENABLE = 'e'
    AND param.ENABLE = 'e'
    AND seq.action_type = 'SummonEnemy'
    AND param.mst_enemy_character_id = '{target_id}'
GROUP BY content_type
ORDER BY stage_count DESC;
```

---

## Step 5: 出現シーケンスパターン分析

### 5-A: condition_type別集計

```sql
SELECT
    condition_type,
    COUNT(*) as usage_count,
    COUNT(DISTINCT sequence_set_id) as ingame_count
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') seq
JOIN read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') param
    ON seq.action_value = param.id
WHERE seq.ENABLE = 'e'
    AND param.ENABLE = 'e'
    AND seq.action_type = 'SummonEnemy'
    AND param.mst_enemy_character_id = '{target_id}'
GROUP BY condition_type
ORDER BY usage_count DESC;
```

### 5-B: 対象キャラが登場するシーケンスの詳細全件

```sql
SELECT
    seq.sequence_set_id,
    seq.sequence_element_id,
    seq.condition_type,
    seq.condition_value,
    seq.action_type,
    seq.action_value,
    seq.summon_position,
    seq.summon_count,
    seq.summon_interval,
    seq.enemy_hp_coef,
    seq.sequence_group_id
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') seq
WHERE seq.ENABLE = 'e'
    AND seq.sequence_set_id IN ({ingame_id_list})
ORDER BY seq.sequence_set_id, seq.sequence_element_id;
```

### 5-C: 召喚位置（summon_position）の傾向

```sql
SELECT
    seq.summon_position,
    COUNT(*) as count
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') seq
JOIN read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') param
    ON seq.action_value = param.id
WHERE seq.ENABLE = 'e'
    AND param.ENABLE = 'e'
    AND seq.action_type = 'SummonEnemy'
    AND param.mst_enemy_character_id = '{target_id}'
GROUP BY seq.summon_position
ORDER BY count DESC;
```

---

## Step 6: コマ効果使用実績分析

### 6-A: 対象ステージのmst_page_idを取得

```sql
SELECT id, mst_page_id
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE ENABLE = 'e'
    AND id IN ({ingame_id_list});
```

### 6-B: koma1〜4のeffect_type集計（UNION方式）

```sql
SELECT effect_type, SUM(cnt) as total_count
FROM (
    SELECT koma1_effect_type as effect_type, COUNT(*) as cnt
    FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ig
    JOIN read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') kl
        ON kl.mst_page_id = ig.mst_page_id
    WHERE ig.ENABLE = 'e' AND kl.ENABLE = 'e'
        AND ig.id IN ({ingame_id_list})
        AND kl.koma1_effect_type IS NOT NULL
        AND kl.koma1_effect_type != 'None'
        AND kl.koma1_effect_type != ''
    GROUP BY effect_type

    UNION ALL

    SELECT koma2_effect_type as effect_type, COUNT(*) as cnt
    FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ig
    JOIN read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') kl
        ON kl.mst_page_id = ig.mst_page_id
    WHERE ig.ENABLE = 'e' AND kl.ENABLE = 'e'
        AND ig.id IN ({ingame_id_list})
        AND kl.koma2_effect_type IS NOT NULL
        AND kl.koma2_effect_type != 'None'
        AND kl.koma2_effect_type != ''
    GROUP BY effect_type

    UNION ALL

    SELECT koma3_effect_type as effect_type, COUNT(*) as cnt
    FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ig
    JOIN read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') kl
        ON kl.mst_page_id = ig.mst_page_id
    WHERE ig.ENABLE = 'e' AND kl.ENABLE = 'e'
        AND ig.id IN ({ingame_id_list})
        AND kl.koma3_effect_type IS NOT NULL
        AND kl.koma3_effect_type != 'None'
        AND kl.koma3_effect_type != ''
    GROUP BY effect_type

    UNION ALL

    SELECT koma4_effect_type as effect_type, COUNT(*) as cnt
    FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ig
    JOIN read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') kl
        ON kl.mst_page_id = ig.mst_page_id
    WHERE ig.ENABLE = 'e' AND kl.ENABLE = 'e'
        AND ig.id IN ({ingame_id_list})
        AND kl.koma4_effect_type IS NOT NULL
        AND kl.koma4_effect_type != 'None'
        AND kl.koma4_effect_type != ''
    GROUP BY effect_type
)
GROUP BY effect_type
ORDER BY total_count DESC;
```

### 6-C: コマ効果の代表ステージを取得（上位 N 件）

```sql
-- koma1_effect_type の代表ステージ例（koma2〜4も同様に実行可）
SELECT DISTINCT
    ig.id as ingame_id,
    kl.koma1_effect_type as effect_type,
    kl.koma1_effect_target_side as target_side
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ig
JOIN read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') kl
    ON kl.mst_page_id = ig.mst_page_id
WHERE ig.ENABLE = 'e' AND kl.ENABLE = 'e'
    AND ig.id IN ({ingame_id_list})
    AND kl.koma1_effect_type IS NOT NULL
    AND kl.koma1_effect_type != 'None'
    AND kl.koma1_effect_type != ''
    AND kl.koma1_effect_type = '{target_effect_type}'
LIMIT 5;
```

---

## カラム確認コマンド

クエリ実行前に必ずカラム名を確認してください。

```bash
# MstEnemyCharacter のカラム確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_enemy_characters

# MstEnemyStageParameter のカラム確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_enemy_stage_parameters

# MstAttack のカラム確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_attacks

# MstAttackElement のカラム確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_attack_elements

# MstAutoPlayerSequence のカラム確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_auto_player_sequences

# MstInGame のカラム確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_in_games

# MstKomaLine のカラム確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_koma_lines

# MstPage のカラム確認
.claude/skills/masterdata-explorer/scripts/search_schema.sh columns mst_pages
```
