# 類似データ参照用 DuckDB クエリ集

Step 1（既存データの参照）で使用するDuckDBクエリ集。
同種ステージの既存データを参照し、値のパターンを把握する。

---

## 基本的な使い方

```bash
# DuckDBの基本構文
duckdb -c "SELECT ... FROM read_csv('projects/glow-masterdata/{テーブル}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE ...;"
```

---

## Step 1 推奨クエリ: 同種ステージから参考データを一括取得

### イベントクエスト（charaget）の参考データ取得

```bash
# MstInGame: 同種ステージの倍率設定を確認
duckdb -c "
SELECT id, bgm_asset_key, boss_bgm_asset_key,
       normal_enemy_hp_coef, normal_enemy_attack_coef, normal_enemy_speed_coef,
       boss_enemy_hp_coef, boss_enemy_attack_coef, boss_enemy_speed_coef,
       boss_count, boss_mst_enemy_stage_parameter_id
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'event_%charaget%'
LIMIT 5;
"

# MstEnemyOutpost: 同種ステージの砦HP確認
duckdb -c "
SELECT id, hp, is_damage_invalidation, artwork_asset_key
FROM read_csv('projects/glow-masterdata/MstEnemyOutpost.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'event_%charaget%'
LIMIT 5;
"
```

### チャレンジ / サベージの参考データ

```bash
# チャレンジ（SpeedAttackルールの確認）
duckdb -c "
SELECT id, content_type, target_id, rule_type, rule_value, start_at, end_at
FROM read_csv('projects/glow-masterdata/MstInGameSpecialRule.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE target_id LIKE 'event_%challenge%'
LIMIT 10;
"

# サベージのシーケンス行数確認
duckdb -c "
SELECT sequence_set_id, COUNT(*) as row_count
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id LIKE 'event_%savage%'
GROUP BY sequence_set_id
ORDER BY row_count DESC
LIMIT 5;
"
```

### レイドの参考データ

```bash
# レイドのシーケンスグループ構造を確認
duckdb -c "
SELECT sequence_set_id, sequence_group_id, sequence_element_id, condition_type, condition_value, action_type, action_value, defeated_score
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id LIKE 'raid_%'
ORDER BY sequence_set_id, sequence_group_id, sequence_element_id
LIMIT 20;
"
```

---

## 既存MstEnemyCharacterのIDを確認するクエリ

```bash
# シリーズ別のキャラ確認（例: KAI系）
duckdb -c "
SELECT id, mst_series_id, asset_key, is_phantomized
FROM read_csv('projects/glow-masterdata/MstEnemyCharacter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE '%kai%'
ORDER BY id;
"

# プレイヤーキャラ確認（chara_ プレフィックスのもの）
duckdb -c "
SELECT id, mst_series_id
FROM read_csv('projects/glow-masterdata/MstEnemyCharacter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'chara_%{シリーズ}%'
ORDER BY id;
"
```

---

## 既存シーケンスのグループ構造を把握するクエリ

```bash
# 特定インゲームのシーケンス全行を取得
duckdb -c "
SELECT id, sequence_set_id, sequence_group_id, sequence_element_id,
       condition_type, condition_value, action_type, action_value, summon_count,
       summon_position, move_start_condition_type, move_start_condition_value,
       aura_type, enemy_hp_coef, enemy_attack_coef, enemy_speed_coef,
       override_drop_battle_point, defeated_score
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id = '{INGAME_ID}'
ORDER BY sequence_group_id, sequence_element_id;
"

# 同種ステージのシーケンス行の典型パターン確認（詳細）
duckdb -c "
SELECT sequence_set_id, sequence_group_id, condition_type, condition_value,
       action_type, action_value, summon_count, summon_interval,
       move_start_condition_type, aura_type, enemy_hp_coef, enemy_attack_coef
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id LIKE 'event_%charaget%'
ORDER BY sequence_set_id, sequence_group_id, sequence_element_id
LIMIT 20;
"
```

---

## 既存MstEnemyStageParameterの確認クエリ

```bash
# シリーズ別のパラメータ確認（例: KAI系）
duckdb -c "
SELECT id, mst_enemy_character_id, character_unit_kind, color,
       hp, move_speed, attack_power, attack_combo_cycle, drop_battle_point
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE '%kai1%'
ORDER BY id;
"

# ボスパラメータの確認
duckdb -c "
SELECT id, mst_enemy_character_id, character_unit_kind, color,
       hp, move_speed, attack_power, damage_knock_back_count,
       mstTransformationEnemyStageParameterId, transformationConditionType
FROM read_csv('projects/glow-masterdata/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE character_unit_kind = 'Boss' AND id LIKE 'c_%kai%'
LIMIT 10;
"
```

---

## コマライン（KomaLine）の参考クエリ

```bash
# 同種ステージのコマライン確認
duckdb -c "
SELECT id, mst_page_id, row, height, koma_line_layout_asset_key,
       koma1_asset_key, koma1_width, koma1_effect_type,
       koma2_asset_key, koma2_width, koma2_effect_type
FROM read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE mst_page_id LIKE 'event_%charaget%'
ORDER BY mst_page_id, row
LIMIT 10;
"
```

---

## 最新リリースキーを確認するクエリ

```bash
# 最新のリリースキー一覧（全テーブル共通で参照）
duckdb -c "
SELECT DISTINCT release_key
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE)
ORDER BY release_key DESC
LIMIT 10;
"

# 特定リリースキーのデータ数確認
duckdb -c "
SELECT release_key, COUNT(*) as count
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE)
GROUP BY release_key
ORDER BY release_key DESC
LIMIT 10;
"
```

---

## MstStageEventSetting / MstStageEventReward の参考クエリ

```bash
# 同種ステージの報酬設定確認
duckdb -c "
SELECT id, mst_stage_id, reward_category, resource_type, resource_id, resource_amount, percentage, sort_order
FROM read_csv('projects/glow-masterdata/MstStageEventReward.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE mst_stage_id LIKE 'event_%charaget%'
LIMIT 10;
"

# イベント設定（クリア回数・リセット）確認
duckdb -c "
SELECT id, mst_stage_id, reset_type, clearable_count, start_at, end_at, background_asset_key
FROM read_csv('projects/glow-masterdata/MstStageEventSetting.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE mst_stage_id LIKE 'event_%'
LIMIT 10;
"
```

---

## BGMキーの確認クエリ

```bash
# よく使われるBGMキー一覧
duckdb -c "
SELECT DISTINCT bgm_asset_key, boss_bgm_asset_key, COUNT(*) as usage_count
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
GROUP BY bgm_asset_key, boss_bgm_asset_key
ORDER BY usage_count DESC
LIMIT 10;
"
```

---

## MstStage の参考クエリ

```bash
# 同種ステージのMstStage設定確認
duckdb -c "
SELECT id, mst_quest_id, mst_in_game_id, stage_number, recommended_level,
       cost_stamina, exp, coin, auto_lap_type, max_auto_lap_count
FROM read_csv('projects/glow-masterdata/MstStage.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'event_%charaget%'
LIMIT 5;
"
```
