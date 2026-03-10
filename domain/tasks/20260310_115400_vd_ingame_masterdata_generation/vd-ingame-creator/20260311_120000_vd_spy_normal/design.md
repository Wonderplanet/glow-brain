# 限界チャレンジ（VD）インゲームマスタデータ設計書

## 基本情報

- 生成日時: 2026-03-11 12:00:00
- 作品ID: `spy`
- ブロック種別: `normal`
- release_key: `202509010`（spy bossブロックと同一バッチ）

---

## 生成するインゲームID

- ID: `vd_spy_normal_00001`
- ブロック種別: normal
- ボスキャラ: なし（normalブロック）
- 雑魚キャラ: 密輸組織の残党（`enemy_spy_00001`）、ファントム（`enemy_glo_00001`）

---

## MstEnemyStageParameter 敵パラメータ設計

> **注意**: 以下の2件は既存の `generated/ファントムマスター/MstEnemyStageParameter.csv` に収録済み。
> 今回新規行は生成しない（参照のみ）。

| MstEnemyStageParameter.id | 役割 | mst_enemy_character_id | 属性 | HP | 攻撃力 | 速度 | drop_battle_point |
|---------------------------|------|------------------------|------|----|--------|------|-------------------|
| `e_spy_00001_vd_Normal_Blue` | 雑魚A（密輸組織の残党） | `enemy_spy_00001` | Blue | 10,000 | 50 | 34 | 300 |
| `e_glo_00001_vd_Normal_Colorless` | 雑魚B（ファントム） | `enemy_glo_00001` | Colorless | 5,000 | 100 | 34 | 150 |

---

## MstAutoPlayerSequence シーケンス設計（normalブロック）

- 合計登場数: **18体**（最低15体以上の要件を満たす）
- フェーズ切り替え: なし（`SwitchSequenceGroup` 不使用）
- `sequence_group_id`: 空（デフォルトグループ）

| id | sequence_element_id | condition_type | condition_value | action_value（MstEnemyStageParameter.id） | summon_count | 備考 |
|----|---------------------|----------------|-----------------|------------------------------------------|--------------|------|
| `vd_spy_normal_00001_1` | 1 | ElapsedTime | 250 | `e_spy_00001_vd_Normal_Blue` | 5 | 密輸組織の残党 初回波 |
| `vd_spy_normal_00001_2` | 2 | ElapsedTime | 1500 | `e_glo_00001_vd_Normal_Colorless` | 5 | ファントム 第2波 |
| `vd_spy_normal_00001_3` | 3 | ElapsedTime | 3000 | `e_spy_00001_vd_Normal_Blue` | 5 | 密輸組織の残党 第3波 |
| `vd_spy_normal_00001_4` | 4 | ElapsedTime | 4500 | `e_glo_00001_vd_Normal_Colorless` | 3 | ファントム 第4波 |

合計: 5 + 5 + 5 + 3 = **18体** ✓

---

## MstKomaLine 構成（normalブロック: 3行固定）

各行ごとに12パターンから独立ランダム選択。height合計=1.00（0.33+0.33+0.34）。

| row | height | 選択パターン | コマ数 | koma1_width | koma2_width | koma3_width | koma4_width |
|-----|--------|-------------|--------|-------------|-------------|-------------|-------------|
| 1 | 0.33 | パターン9「中央広い」 | 3コマ | 0.25 | 0.50 | 0.25 | - |
| 2 | 0.33 | パターン4「がっつり右長2コマ」 | 2コマ | 0.75 | 0.25 | - | - |
| 3 | 0.34 | パターン12「4等分」 | 4コマ | 0.25 | 0.25 | 0.25 | 0.25 |

- `koma_effect_type`: すべて `None`
- `koma1_effect_target_side`: すべて `All`（コマ効果なしでも必須）

---

## MstEnemyOutpost 設計

| id | hp | is_damage_invalidation |
|----|----|------------------------|
| `vd_spy_normal_00001` | 100 | 空（ダメージ有効） |

---

## MstPage 設計

| id |
|----|
| `vd_spy_normal_00001` |

---

## MstInGame 主要設定

| カラム | 値 |
|--------|-----|
| id | `vd_spy_normal_00001` |
| mst_auto_player_sequence_set_id | `vd_spy_normal_00001` |
| bgm_asset_key | `SSE_SBG_003_010` |
| boss_bgm_asset_key | 空（normalブロックのためなし） |
| loop_background_asset_key | `koma_background_vd_00001`（フロア0以上） |
| mst_page_id | `vd_spy_normal_00001` |
| mst_enemy_outpost_id | `vd_spy_normal_00001` |
| boss_mst_enemy_stage_parameter_id | 空（normalブロック） |
| boss_count | 空 |
| normal_enemy_hp_coef | 1 |
| normal_enemy_attack_coef | 1 |
| normal_enemy_speed_coef | 1 |
| boss_enemy_hp_coef | 1 |
| boss_enemy_attack_coef | 1 |
| boss_enemy_speed_coef | 1 |
| release_key | 202509010 |

---

## 参照した既存データ

- `generated/ファントムマスター/MstEnemyStageParameter.csv`（spy雑魚パラメータ取得）
- `specs/作品別登場キャラ一覧.csv`（spy ノーマルブロック登場キャラ確認）

---

## 不確定事項・要確認事項

なし
