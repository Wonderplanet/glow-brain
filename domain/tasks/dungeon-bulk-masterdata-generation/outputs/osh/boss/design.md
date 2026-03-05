# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ブロック種別: boss（ボスブロック）
- シリーズ: 【推しの子】（osh）

## インゲームID
- インゲームID: `dungeon_osh_boss_00001`
- 命名根拠: `dungeon_{シリーズ}_boss_{連番5桁}` の命名規則に従う

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（アイ / 無属性）、雑魚1体（Yellow汎用敵） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon boss固定値） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（dungeon boss固定値） |
| MstAutoPlayerSequence | 3件 | 3ウェーブ構成 |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計

| カラム | 値 | 根拠 |
|--------|-----|------|
| `id` | `dungeon_osh_boss_00001` | インゲームID |
| `bgm_asset_key` | `SSE_SBG_003_003` | 【推しの子】メインBGM |
| `boss_bgm_asset_key` | （空） | ボス専用BGMなし |
| `loop_background_asset_key` | `osh_00001` | 【推しの子】背景アセット |
| `player_outpost_asset_key` | （空） | デフォルト |
| `mst_page_id` | `dungeon_osh_boss_00001` | MstInGame.idと同一 |
| `mst_enemy_outpost_id` | `dungeon_osh_boss_00001` | MstInGame.idと同一 |
| `mst_defense_target_id` | （空） | 未使用 |
| `boss_mst_enemy_stage_parameter_id` | `c_osh_00001_general_osh_n_Boss_Colorless` | ボスパラメータID |
| `boss_count` | `1` | ボス1体 |
| `normal_enemy_hp_coef` | `1` | 全体倍率（等倍） |
| `normal_enemy_attack_coef` | `1` | 全体倍率（等倍） |
| `normal_enemy_speed_coef` | `1` | 全体倍率（等倍） |
| `boss_enemy_hp_coef` | `1` | 全体倍率（等倍） |
| `boss_enemy_attack_coef` | `1` | 全体倍率（等倍） |
| `boss_enemy_speed_coef` | `1` | 全体倍率（等倍） |
| `release_key` | `999999999` | 開発・テスト用 |
| `result_tips.ja` | （空） | バトルヒント未設定 |
| `description.ja` | 強力なボスを倒すまでは、敵ゲートにダメージが入らないぞ！まずはボスを撃破することを優先しよう！無属性の敵も登場するぞ！ | ステージ説明文 |

## MstEnemyStageParameter 敵パラメータ設計

| id | 役割 | mst_enemy_character_id | character_unit_kind | role_type | color | HP（base） | move_speed | attack_power | attack_combo_cycle | damage_knock_back_count | drop_battle_point |
|----|------|----------------------|---------------------|-----------|-------|-----------|-----------|-------------|-------------------|------------------------|-----------------|
| `c_osh_00001_general_osh_n_Boss_Colorless` | ボス（B小町不動のセンター アイ） | `chara_osh_00001` | Boss | Technical | Colorless | 1,000 | 20 | 100 | 6 | 3 | 500 |
| `e_glo_00001_general_osh_n_Normal_Yellow` | 護衛雑魚（汎用敵1） | `enemy_glo_00001` | Normal | Attack | Yellow | 1,000 | 34 | 100 | 1 | 0 | 100 |

### 最終パラメータ（倍率乗算後）

**ボス（アイ）:**
- 最終HP = 1,000 × 1（MstInGame.boss_enemy_hp_coef）× 50（シーケンス）= **50,000**
- 最終ATK = 100 × 1（MstInGame.boss_enemy_attack_coef）× 20（シーケンス）= **2,000**

**護衛雑魚（Yellow汎用敵）:**
- 最終HP = 1,000 × 1（MstInGame.normal_enemy_hp_coef）× 5（シーケンス）= **5,000**
- 最終ATK = 100 × 1（MstInGame.normal_enemy_attack_coef）× 5（シーケンス）= **500**

## MstAutoPlayerSequence ウェーブ構成設計

- 総ウェーブ数: 3行
- グループ切り替え: **なし**（デフォルトグループのみ）

| 行 | sequence_element_id | condition_type | condition_value | action_type | action_value | summon_count | summon_position | aura_type | enemy_hp_coef | enemy_attack_coef | is_summon_unit_outpost_damage_invalidation | 備考 |
|----|---------------------|---------------|----------------|------------|-------------|-------------|----------------|---------|--------------|------------------|------------------------------------------|------|
| 1 | 1 | ElapsedTime | 0 | SummonEnemy | c_osh_00001_general_osh_n_Boss_Colorless | 1 | 1.7 | Boss | 50 | 20 | 1 | ボス（アイ）を砦前に配置・ゲートダメージ無効 |
| 2 | 2 | ElapsedTime | 20 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 2 | （空） | Default | 5 | 5 | 0 | 2秒後・護衛雑魚2体出現 |
| 3 | 3 | ElapsedTime | 50 | SummonEnemy | e_glo_00001_general_osh_n_Normal_Yellow | 1 | （空） | Default | 5 | 5 | 0 | 5秒後・追加護衛雑魚1体 |

> 注意: condition_value はシステム上 100ms 単位のため、2秒=20、5秒=50 となる。

## MstPage / MstKomaLine 構成

- ページID: `dungeon_osh_boss_00001`
- コマ行数: **1行**（dungeon bossブロック固定）
- コマ効果: **なし（None）**
- row=1: 全幅1コマ（koma1_width=1.0、layout=1）、背景アセット `osh_00001`

## MstEnemyOutpost 設計

| カラム | 値 |
|--------|-----|
| `id` | `dungeon_osh_boss_00001` |
| `hp` | `1,000`（dungeon boss固定値） |
| `is_damage_invalidation` | （空）（ダメージ有効。ゲートダメージ無効はシーケンスのis_summon_unit_outpost_damage_invalidationで制御） |
| `outpost_asset_key` | （空） |
| `artwork_asset_key` | （空） |

## 参照した既存データ

- spy normalブロック（`dungeon_spy_normal_00001`）を参考として参照
- release_key は `999999999`（開発・テスト用）を使用

## 不確定事項・要確認事項

- ボス `is_summon_unit_outpost_damage_invalidation=1` の設定: 要件テキスト通り、ボス召喚時のシーケンス行（行1）で `1` を設定
- `chara_osh_00001` のmst_enemy_character_idとして指定（プレイヤーキャラが敵として登場するc_プレフィックス）
- `enemy_glo_00001` のmst_enemy_character_idとして指定（汎用敵専用e_プレフィックス）
