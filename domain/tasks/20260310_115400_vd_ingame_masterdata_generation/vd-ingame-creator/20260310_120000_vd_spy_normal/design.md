# 限界チャレンジ（VD）インゲームマスタデータ設計書

## 基本情報

- 生成日時: 2026-03-10 12:00:00
- 作品ID: `spy`
- ブロック種別: `normal`

---

## 生成するインゲームID

- ID: `vd_spy_normal_00001`
- ブロック種別: normal
- ボスキャラ: なし（normalブロックはボスなし）
- 雑魚キャラ: `enemy_spy_00001`（密輸組織の残党）、`enemy_glo_00001`（ファントム）

---

## MstEnemyStageParameter 使用パラメータ（既存データ流用）

> 提供済みMstEnemyStageParameterをそのまま使用します。新規行の追加はありません。

| MstEnemyStageParameter.id | mst_enemy_character_id | character_unit_kind | color | hp | attack_power |
|---------------------------|----------------------|---------------------|-------|----|-------------|
| `e_spy_00001_vd_Boss_Blue` | enemy_spy_00001 | Boss | Blue | 10,000 | 50 |
| `e_glo_00001_vd_Normal_Colorless` | enemy_glo_00001 | Normal | Colorless | 5,000 | 100 |

---

## MstAutoPlayerSequence シーケンス設計（normalブロック）

normalブロック仕様：
- ボスなし（`boss_mst_enemy_stage_parameter_id` は空）
- 全行 `aura_type=Default`
- 最低15体以上の雑魚が登場するよう設計
- フェーズ切り替えなし（`SwitchSequenceGroup` 禁止）

| 行 | id | condition_type | condition_value | action_type | action_value | summon_count | summon_interval | aura_type |
|---|---|---|---|---|---|---|---|---|
| 1 | vd_spy_normal_00001_1 | ElapsedTime | 250 | SummonEnemy | e_spy_00001_vd_Boss_Blue | 3 | 1500 | Default |
| 2 | vd_spy_normal_00001_2 | ElapsedTime | 1500 | SummonEnemy | e_glo_00001_vd_Normal_Colorless | 3 | 1500 | Default |
| 3 | vd_spy_normal_00001_3 | ElapsedTime | 3000 | SummonEnemy | e_spy_00001_vd_Boss_Blue | 3 | 1500 | Default |
| 4 | vd_spy_normal_00001_4 | ElapsedTime | 6000 | SummonEnemy | e_glo_00001_vd_Normal_Colorless | 3 | 1500 | Default |
| 5 | vd_spy_normal_00001_5 | ElapsedTime | 9000 | SummonEnemy | e_spy_00001_vd_Boss_Blue | 3 | 1500 | Default |
| 6 | vd_spy_normal_00001_6 | ElapsedTime | 12000 | SummonEnemy | e_glo_00001_vd_Normal_Colorless | 3 | 1500 | Default |

合計出現数: 18体（15体以上の条件を満たす）

---

## MstKomaLine 構成

| 項目 | 値 |
|------|---|
| ブロック種別 | normal |
| コマ数 | 2（ランダム1〜4の中から選択） |
| koma1_width | 0.5 |
| koma2_width | 0.5 |
| 合計幅 | 1.0 |

---

## その他の固定値

| テーブル | カラム | 値 |
|---------|--------|---|
| MstEnemyOutpost | hp | 100（normalブロック固定） |
| MstEnemyOutpost | is_damage_invalidation | 空 |
| MstInGame | bgm_asset_key | `SSE_SBG_003_010` |
| MstInGame | boss_mst_enemy_stage_parameter_id | 空（normalブロック） |
| MstInGame | koma_background_asset_key | `koma_background_vd_00001` |
| MstInGame | normal_enemy_hp_coef | 1.0 |
| MstInGame | normal_enemy_attack_coef | 1.0 |
| MstInGame | normal_enemy_speed_coef | 1 |
| MstInGame | boss_enemy_hp_coef | 1.0 |
| MstInGame | boss_enemy_attack_coef | 1.0 |
| MstInGame | boss_enemy_speed_coef | 1 |

---

## 参照した既存データ

- 既存の `vd_spy_normal_*` データは存在しないため、連番 `00001` から開始
- 既存VDデータ（DuckDB確認）: `projects/glow-masterdata/MstInGame.csv` にVD系エントリなし
- sheet_schemaカラム定義: 各テーブルのENABLE行（3行目）を参照済み

---

## 不確定事項・要確認事項

- `e_spy_00001_vd_Boss_Blue` のcharacter_unit_kind が `Boss` となっているが、normalブロックの雑魚として使用する（ユーザー指定のMstEnemyStageParameterをそのまま流用するため）
- release_key: 既存のspyデータに合わせ `202509010` を使用予定
