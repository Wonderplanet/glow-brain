# VD 専用 DuckDB クエリ集

Step 1（既存データの参照）で使用するDuckDBクエリ集。
VD（限界チャレンジ）の既存データを参照し、値のパターンを把握する。

---

## 基本構文

```bash
duckdb -c "SELECT ... FROM read_csv('projects/glow-masterdata/{テーブル}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE ...;"
```

---

## VD MstInGame の全件確認

```bash
duckdb -c "
SELECT id, bgm_asset_key, boss_bgm_asset_key, content_type, stage_type,
       boss_mst_enemy_stage_parameter_id, mst_page_id, mst_enemy_outpost_id,
       normal_enemy_hp_coef, normal_enemy_attack_coef, boss_enemy_hp_coef, boss_enemy_attack_coef
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'vd_%'
ORDER BY id;
"
```

## VD MstEnemyOutpost の確認

```bash
duckdb -c "
SELECT id, hp, is_damage_invalidation, artwork_asset_key
FROM read_csv('projects/glow-masterdata/MstEnemyOutpost.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'vd_%'
ORDER BY id;
"
```

## VD MstAutoPlayerSequence の確認

```bash
duckdb -c "
SELECT sequence_set_id, sequence_element_id, condition_type, condition_value,
       action_type, action_value, summon_count, summon_position,
       move_start_condition_type, move_start_condition_value,
       aura_type, enemy_hp_coef, enemy_attack_coef, enemy_speed_coef
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id LIKE 'vd_%'
ORDER BY sequence_set_id, sequence_element_id;
"
```

## 特定VDインゲームのシーケンス詳細

```bash
# {INGAME_ID} を実際のIDに置換
duckdb -c "
SELECT id, sequence_set_id, sequence_element_id,
       condition_type, condition_value, action_type, action_value,
       summon_count, summon_position, aura_type,
       enemy_hp_coef, enemy_attack_coef
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id = 'vd_kai_boss_00001'
ORDER BY sequence_element_id;
"
```

## VD MstEnemyStageParameter の確認

```bash
# VD専用キュレーション済みCSVからパラメータを確認
duckdb -c "
SELECT id, mst_enemy_character_id, character_unit_kind, color,
       hp, move_speed, attack_power, attack_combo_cycle, drop_battle_point
FROM read_csv('domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_all/data/MstEnemyStageParameter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
ORDER BY id;
"
```

## VD MstKomaLine の確認

```bash
duckdb -c "
SELECT id, mst_page_id, row, height,
       koma1_width, koma1_effect_type, koma1_effect_target_side,
       koma2_width, koma2_effect_type,
       koma3_width, koma3_effect_type,
       koma4_width, koma4_effect_type
FROM read_csv('projects/glow-masterdata/MstKomaLine.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE mst_page_id LIKE 'vd_%'
ORDER BY mst_page_id, row;
"
```

---

## 既存 dungeon_ データの参照（VD設計の参考）

VDが存在しない場合は、既存の `dungeon_` データを参考にする:

```bash
# dungeon_ InGame の確認（boss/normalブロック比較）
duckdb -c "
SELECT id, content_type, stage_type,
       normal_enemy_hp_coef, normal_enemy_attack_coef,
       boss_enemy_hp_coef, boss_enemy_attack_coef,
       boss_mst_enemy_stage_parameter_id
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE 'dungeon_%'
ORDER BY id;
"

# dungeon_ シーケンスの構造確認
duckdb -c "
SELECT sequence_set_id, COUNT(*) as row_count,
       COUNT(CASE WHEN aura_type = 'Boss' THEN 1 END) as boss_rows,
       COUNT(CASE WHEN condition_type = 'InitialSummon' THEN 1 END) as initial_summon_rows
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE sequence_set_id LIKE 'dungeon_%'
GROUP BY sequence_set_id
ORDER BY sequence_set_id;
"
```

---

## 作品別 MstEnemyCharacter の確認

```bash
# 特定作品のキャラ確認（例: KAI系）
duckdb -c "
SELECT id, mst_series_id, asset_key, is_phantomized
FROM read_csv('projects/glow-masterdata/MstEnemyCharacter.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id LIKE '%kai%'
ORDER BY id;
"
```

---

## 最新リリースキーの確認

```bash
duckdb -c "
SELECT DISTINCT release_key
FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE)
ORDER BY release_key DESC
LIMIT 10;
"
```
