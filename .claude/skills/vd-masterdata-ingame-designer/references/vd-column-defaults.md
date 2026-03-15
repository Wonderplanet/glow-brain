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
| `koma_line_layout_asset_key` | 整数（1〜12） | パターン番号を指定。design.mdの「パターンN」の番号がそのまま値になる（例: パターン6→`6`、パターン8→`8`、パターン12→`12`）。全対応表は下記を参照。 |
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

### koma_line_layout_asset_key 全パターン対応表

> ソース: `projects/glow-masterdata/MstKomaLine.csv` 全1,491件から集計（2026-03-12確認）

| パターン番号 | コマ数 | 幅パターン（koma1〜4） | 説明 |
|-----------|------|-------------------|------|
| 1 | 1 | 1.0 | 1コマフル幅 |
| 2 | 2 | 0.6, 0.4 | 左広い |
| 3 | 2 | 0.4, 0.6 | 右広い |
| 4 | 2 | 0.75, 0.25 | 左がかなり広い |
| 5 | 2 | 0.25, 0.75 | 右がかなり広い |
| 6 | 2 | 0.5, 0.5 | 2等分（完全均等）|
| 7 | 3 | 0.33, 0.34, 0.33 | 3等分 |
| 8 | 3 | 0.5, 0.25, 0.25 | 左広い・右2等分 |
| 9 | 3 | 0.25, 0.5, 0.25 | 中央広い |
| 10 | 3 | 0.25, 0.25, 0.5 | 右広い・左2等分 |
| 11 | 3 | 0.4, 0.2, 0.4 | 左右広い・中央狭い |
| 12 | 4 | 0.25, 0.25, 0.25, 0.25 | 4等分（完全均等）|

---

## MstEnemyOutpost（要確認 1項目）

| カラム | 空欄件数/全体 | 判定 | 備考 |
|--------|-------------|------|------|
| `is_damage_invalidation` | 520/539件が空欄 | 修正不要 | 基本は空欄 |
| `outpost_asset_key` | 505/539件が空欄 | 修正不要 | 空欄が多数派 |
| `artwork_asset_key` | 48/539件が空欄 | **要確認** | 大半は値あり。VD用アセットキーがあれば設定推奨（例: `aya_0001` 等）。アセット担当者に確認する |

---

## MstInGame（重要な設定値）

| カラム | 設定値 | 備考 |
|--------|-------|------|
| `mst_auto_player_sequence_id` | `""`（空文字） | レガシーカラム。値を設定するとバリデーションエラー |
| `mst_auto_player_sequence_set_id` | `{block_id}` | MstInGame.id と同値（必須） |
| `mst_defense_target_id` | `__NULL__` | 空文字ではなく`__NULL__`（NULL値）が必須 |
| `boss_bgm_asset_key` | `""`（空文字） | BGM切り替えなし。VD全ブロック共通 |
| 全coefカラム×6 | `1.0` | `normal/boss_enemy_hp/attack/speed_coef` 全て1.0固定 |
| `loop_background_asset_key` | Normal原則空文字 / Boss作品対応アセット | 下表参照 |

### loop_background_asset_key 作品別設定値

| 種別 | 作品 | 設定値 |
|------|------|-------|
| Normal例外 | jig | `jig_00002` |
| Normal例外 | mag | `mag_00004` |
| Boss | kai | `kai_00001` |
| Boss | dan | `dan_00001` |
| その他Normal | 全作品 | `""`（空文字） |
| その他Boss | 未定作品 | `""`（空文字） |
