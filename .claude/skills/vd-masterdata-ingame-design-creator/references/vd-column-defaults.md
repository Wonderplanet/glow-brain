# VDデザインフェーズ カラムデフォルト値一覧

設計書（design.md）作成時に設定が必要なカラムのデフォルト値一覧。
既存データ調査に基づき、空欄になりがちなカラムをまとめた。

ソース調査: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/knowledge/vd_ingame_csv_空欄カラム調査結果.md`

---

## MstAutoPlayerSequence（修正必要カラム 7項目）

| カラム | デフォルト値 | 備考 |
|--------|------------|------|
| `summon_animation_type` | `None` | 他の値: `Fall` / `Fall0` / `Fall4`。VDは`None` |
| `move_start_condition_type` | `None` | 行2・行3が対象（行1はすでに`Damage`等が入る）。他: `ElapsedTime` / `Damage` / `EnterTargetKoma` 等 |
| `move_stop_condition_type` | `None` | 既存データは全件`None`のみ |
| `move_restart_condition_type` | `None` | 既存データは全件`None`のみ |
| `death_type` | `Normal` | 他の値: `Escape`。VDは`Normal` |
| `defeated_score` | `0` | 既存データは全件数値が入っている |
| `deactivation_condition_type` | `None` | 他: `FriendUnitDead` / `OutpostHpPercentage` / `EnterTargetKomaIndex` / `ElapsedTime` |

### 修正不要（空欄でOK）
- `sequence_group_id` / `priority_sequence_element_id` / `action_value2`
- `summon_position`（Boss行のみ1.7が必要、Normal行は空欄でOK）
- `move_start_condition_value` / `move_stop_condition_value` / `move_restart_condition_value`
- `move_loop_count` / `is_summon_unit_outpost_damage_invalidation`
- `last_boss_trigger` / `override_drop_battle_point` / `action_delay`
- `deactivation_condition_value`

---

## MstKomaLine（修正必要カラム 8項目）

| カラム | デフォルト値 | 備考 |
|--------|------------|------|
| `koma_line_layout_asset_key` | 数値（1〜6） | 既存例: `1`, `2`, `3`, `6` 等。VDに適した番号を設定 |
| `koma1_asset_key` | 作品固有アセットキー | **[series-koma-assets.csv](series-koma-assets.csv) を参照**して作品IDに合った値を設定する |
| `koma1_back_ground_offset` | 作品別推奨値 | **[koma-background-offset.md](koma-background-offset.md) を参照**して推奨仮値を設定する |
| `koma1_effect_parameter1` | `0` | エフェクトなし（`None`）の場合は`0` |
| `koma1_effect_parameter2` | `0` | エフェクトなし（`None`）の場合は`0` |
| `koma1_effect_target_colors` | `All` | 既存データは`All`が基本 |
| `koma1_effect_target_roles` | `All` | 既存データは`All`が基本 |
| `koma2_effect_type`〜`koma4_effect_type` | `None` | koma2〜4が存在しない場合でも`None`が必要（既存データ0件空欄） |

### 修正不要（空欄/NULLでOK）
- `koma2_back_ground_offset`〜`koma4_back_ground_offset`：koma2〜4が存在しない場合はNULL（`__NULL__`）または空欄
- `koma2_effect_target_side`〜`koma4_effect_target_side`：koma2〜4が存在しない場合は空欄

---

## MstEnemyOutpost（要確認 1項目）

| カラム | 空欄件数/全体 | 判定 | 備考 |
|--------|-------------|------|------|
| `is_damage_invalidation` | 520/539件が空欄 | 修正不要 | 基本は空欄 |
| `outpost_asset_key` | 505/539件が空欄 | 修正不要 | 空欄が多数派 |
| `artwork_asset_key` | 48/539件が空欄 | **要確認** | 大半は値あり。VD用アセットキーがあれば設定推奨（例: `aya_0001` 等）。アセット担当者に確認する |
