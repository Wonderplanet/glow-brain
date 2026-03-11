# VDインゲームCSV 空欄カラム調査結果

調査日: 2026-03-11
対象: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/vd-ingame-creator/vd_aya_boss_00001/generated/`
比較基準: `projects/glow-masterdata/` の既存データ（非VDコンテンツ）

---

## 調査方法

生成CSVの空欄カラムについて、`projects/glow-masterdata` の各CSVに存在する既存データ（4641件以上）をDuckDBで全件集計し、空欄の有無を確認。「既存データに空欄が1件もないカラム」は値が必須と判断。

---

## 結果サマリ

| テーブル | 修正必要カラム数 | 要確認カラム数 | 修正不要カラム数 |
|---------|--------------|-------------|--------------|
| MstAutoPlayerSequence | 7 | 1 | 多数 |
| MstKomaLine | 8 | 0 | 一部 |
| MstEnemyOutpost | 0 | 1 | 2 |
| MstEnemyStageParameter | 0 | 0 | 3 |
| MstInGame | 0 | 0 | 6 |
| MstPage | 0 | 0 | — |

---

## MstAutoPlayerSequence.csv

### 🔴 修正必要（既存データに空欄が0件）

| カラム | 設定値 | 備考 |
|--------|--------|------|
| `summon_animation_type` | `None` | 他の値: `Fall` / `Fall0` / `Fall4`。VDは`None` |
| `move_start_condition_type` | `None` | 行2・行3が対象（行1はすでに`Damage`が入っている）。他の値: `ElapsedTime` / `Damage` / `EnterTargetKoma` / `FoeEnterSameKoma` / `DeadFriendUnitCount` |
| `move_stop_condition_type` | `None` | 既存データは全件`None`のみ |
| `move_restart_condition_type` | `None` | 既存データは全件`None`のみ |
| `death_type` | `Normal` | 他の値: `Escape`。VDは`Normal` |
| `defeated_score` | `0` | 既存データは全件数値が入っている |
| `deactivation_condition_type` | `None` | 他の値: `FriendUnitDead` / `OutpostHpPercentage` / `EnterTargetKomaIndex` / `ElapsedTime` |

### ⚠️ 要確認

| カラム | 現状 | 確認事項 |
|--------|------|---------|
| `summon_interval` | 行1=空欄 / 行2=1500 / 行3=空欄 | 既存データでも空欄あり（summon_count=1や非召喚行）。ただし**行2と行3はどちらも3体召喚（summon_count=3）で行2のみ1500・行3が空欄は不整合**。行3も`1500`にすべきか、または行2を`0`にすべきか確認が必要 |

### ✅ 修正不要（既存データにも空欄あり）

- `sequence_group_id`
- `priority_sequence_element_id`
- `action_value2`
- `summon_position`（Boss行のみ1.7が必要、Normal行は空欄でOK）
- `move_start_condition_value`
- `move_stop_condition_value`
- `move_restart_condition_value`
- `move_loop_count`
- `is_summon_unit_outpost_damage_invalidation`
- `last_boss_trigger`
- `override_drop_battle_point`
- `action_delay`
- `deactivation_condition_value`

---

## MstKomaLine.csv

### 🔴 修正必要（既存データに空欄が0件）

| カラム | 設定値 | 備考 |
|--------|--------|------|
| `koma_line_layout_asset_key` | 数値（1〜6） | 既存例: `1`, `2`, `3`, `6` 等。VDに適した番号を設定 |
| `koma1_asset_key` | 作品固有アセットキー | 既存例: `glo_00001`, `jig_00002` 等。aya作品用のキーを設定 |
| `koma1_back_ground_offset` | 数値 | 既存例: `-1`, `0`, `0.92` 等 |
| `koma1_effect_parameter1` | `0` | エフェクトなし（`None`）の場合は`0` |
| `koma1_effect_parameter2` | `0` | エフェクトなし（`None`）の場合は`0` |
| `koma1_effect_target_colors` | `All` | 既存データは`All`が基本 |
| `koma1_effect_target_roles` | `All` | 既存データは`All`が基本 |
| `koma2_effect_type`〜`koma4_effect_type` | `None` | koma2〜4が存在しない場合でも`None`が必要（既存データ0件空欄） |

### ✅ 修正不要（既存データにも空欄/NULLあり）

- `koma2_back_ground_offset`〜`koma4_back_ground_offset`：koma2〜4が存在しない場合はNULL（`__NULL__`）または空欄でOK（既存で313件NULL）
- `koma2_effect_target_side`〜`koma4_effect_target_side`：koma2〜4が存在しない場合は空欄でOK（171件NULL）

---

## MstEnemyOutpost.csv

| カラム | 空欄件数/全体 | 判定 | 備考 |
|--------|-------------|------|------|
| `is_damage_invalidation` | 520/539件が空欄 | ✅ 修正不要 | 基本は空欄 |
| `outpost_asset_key` | 505/539件が空欄 | ✅ 修正不要 | 空欄が多数派 |
| `artwork_asset_key` | 48/539件が空欄 | ⚠️ 要確認 | 大半は値あり。VD用アセットキーがあれば設定推奨（例: `aya_0001` 等） |

---

## MstEnemyStageParameter.csv

| カラム | 判定 | 備考 |
|--------|------|------|
| `mst_unit_ability_id1` | ✅ 修正不要 | アビリティなしの場合は空欄でOK（既存データも同パターン） |
| `mstTransformationEnemyStageParameterId` | ✅ 修正不要 | `transformationConditionType=None` の場合は空欄でOK |
| `transformationConditionValue` | ✅ 修正不要 | `transformationConditionType=None` の場合は空欄でOK |

---

## MstInGame.csv

| カラム | 判定 | 備考 |
|--------|------|------|
| `boss_bgm_asset_key` | ✅ 修正不要 | 既存データで空欄あり |
| `loop_background_asset_key` | ✅ 修正不要 | 既存データで空欄あり |
| `player_outpost_asset_key` | ✅ 修正不要 | 既存データで空欄あり |
| `mst_defense_target_id` | ✅ 修正不要 | 既存データで空欄あり |
| `result_tips.ja` | ✅ 修正不要 | 既存CSV（20列）にはこの列自体が存在しない新規列。空欄でOK |
| `description.ja` | ✅ 修正不要 | 同上 |

---

## MstPage.csv

全列入力済み。修正不要。

---

## 備考

- 本調査は `vd_aya_boss_00001` を対象としているが、同タスクで生成された他のVDデータ（`vd_gom_boss_00001`、`vd_hut_boss_00001` 等）も同じ空欄パターンを持つため、同様の修正が必要な可能性がある
- `MstKomaLine` の `koma1_asset_key` / `koma_line_layout_asset_key` / `koma1_back_ground_offset` の具体的な値は作品・コンテンツごとに異なるため、アセット担当者への確認が必要
