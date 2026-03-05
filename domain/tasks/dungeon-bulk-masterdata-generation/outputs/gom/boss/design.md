# インゲームマスタデータ設計書

## 基本情報

- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）
- 対象シリーズ: gom（姫様"拷問"の時間です）

## インゲームID命名

- インゲームID: `dungeon_gom_boss_00001`
- 命名根拠: dungeon_{シリーズ}_boss_{連番5桁} の命名規則に従い、gomシリーズ初のボスブロックとして 00001 を付番

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（囚われの王女 姫様）、雑魚1体（バタートースト） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon boss仕様固定値） |
| MstPage | 1件 | id = dungeon_gom_boss_00001 |
| MstKomaLine | 1件 | 1行構成（dungeon boss仕様固定値） |
| MstAutoPlayerSequence | 3件 | ボス初期召喚 + 雑魚2ウェーブ |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計

- `id`: `dungeon_gom_boss_00001`
- `content_type`: dungeon（インゲームのコンテンツ種別）
- `stage_type`: dungeon_boss
- `bgm_asset_key`: `SSE_SBG_003_006`（gom作品専用BGM）
- `loop_background_asset_key`: `gom_00001`
- `player_outpost_asset_key`: `gom_ally_0001`
- `mst_page_id`: `dungeon_gom_boss_00001`
- `mst_enemy_outpost_id`: `dungeon_gom_boss_00001`
- `boss_mst_enemy_stage_parameter_id`: `c_gom_00001_general_n_Boss_Yellow`
- `boss_count`: 1
- `normal_enemy_hp_coef`: 1（MstAutoPlayerSequenceで個別設定）
- `normal_enemy_attack_coef`: 1（MstAutoPlayerSequenceで個別設定）
- `normal_enemy_speed_coef`: 1
- `boss_enemy_hp_coef`: 1（MstAutoPlayerSequenceで個別設定）
- `boss_enemy_attack_coef`: 1（MstAutoPlayerSequenceで個別設定）
- `boss_enemy_speed_coef`: 1
- `release_key`: 999999999（仮値、後で正式なリリースキーに変更）

## MstEnemyStageParameter 敵パラメータ設計

インゲームID短縮形として `gom_dungeon` を使用する（命名規則: dungeon_kai_boss → kai_dungeon 準拠）。

| 識別子 | 役割 | mst_enemy_character_id | character_unit_kind | color | HP | 攻撃力 | 移動速度 | 索敵距離 | ノックバック |
|--------|------|----------------------|---------------------|-------|-----|-------|---------|---------|------------|
| `c_gom_00001_gom_dungeon_Boss_Yellow` | ボス（囚われの王女 姫様） | `chara_gom_00001` | Boss | Yellow | 10,000 | 50 | 25 | 0.16 | 1 |
| `e_gom_00501_gom_dungeon_Normal_Colorless` | 護衛雑魚（バタートースト） | `enemy_gom_00501` | Normal | Colorless | 1,000 | 50 | 34 | 0.14 | 0 |

- ボスHPは既存の `c_gom_00001_general_n_Boss_Yellow` 等と同じ基準値 10,000 を採用
- 攻撃力はdungeonボスの設計方針「通常より高め」に従い、既存の attack_combo_cycle=6 を踏襲
- シーケンスの enemy_hp_coef=1.0、enemy_attack_coef=2.0〜3.0 で最終調整

## MstEnemyOutpost 設計

| 項目 | 値 |
|------|-----|
| id | `dungeon_gom_boss_00001` |
| hp | 1,000（dungeon boss仕様固定値） |
| is_damage_invalidation | 空（ダメージ有効 ※ボス撃破でゲートへのダメージが解放される仕組みはシーケンス側で制御） |
| outpost_asset_key | `gom_00001` |
| artwork_asset_key | `gom_00001` |

> **注意**: ボスが生存している間のゲートダメージ無効は `MstAutoPlayerSequence.is_summon_unit_outpost_damage_invalidation=1` により制御する（MstEnemyOutpostのis_damage_invalidationではなく）。

## MstPage / MstKomaLine 設計

- ページID: `dungeon_gom_boss_00001`
- コマ行数: **1行**（dungeon boss仕様固定値）
- コマアセット: `gom_00001`
- コマ幅: 1.0（1コマ全幅）
- koma_line_layout_asset_key: `1`（1コマ・全幅）
- height: 1.0（1行のみのため全高）
- コマ効果: None

## MstAutoPlayerSequence ウェーブ構成設計

シングルグループ（グループ切り替えなし）・3行構成。

| 行 | id | condition_type | condition_value | action_type | action_value | summon_count | summon_interval | summon_position | move_start_condition_type | move_start_condition_value | is_summon_unit_outpost_damage_invalidation | aura_type | enemy_hp_coef | enemy_attack_coef |
|----|-----|---------------|----------------|-------------|--------------|-------------|----------------|----------------|--------------------------|--------------------------|------------------------------------------|-----------|--------------|-----------------|
| 1 | `dungeon_gom_boss_00001_1` | InitialSummon | 0 | SummonEnemy | `c_gom_00001_gom_dungeon_Boss_Yellow` | 1 | | 1.7（砦付近） | Damage | 1 | 1 | Boss | 1.0 | 2.5 |
| 2 | `dungeon_gom_boss_00001_2` | ElapsedTime | 5（500ms） | SummonEnemy | `e_gom_00501_gom_dungeon_Normal_Colorless` | 3 | 300ms（3） | | None | | 0 | Default | 1.5 | 1.5 |
| 3 | `dungeon_gom_boss_00001_3` | ElapsedTime | 20（2000ms） | SummonEnemy | `e_gom_00501_gom_dungeon_Normal_Colorless` | 3 | 300ms（3） | | None | | 0 | Default | 1.5 | 1.5 |

- 行1: ボス「囚われの王女 姫様」を砦付近（summon_position=1.7）に配置し、1ダメージ受けたら移動開始。ボス生存中はゲートへのダメージ無効（is_summon_unit_outpost_damage_invalidation=1）。
- 行2: 500ms後にバタートースト3体・インターバル300msで時間差出現。
- 行3: 2000ms後にバタートースト3体・インターバル300msで追加出現。

## 参照した既存データ

- `c_gom_00001_general_n_Boss_Yellow`: HP=10,000、move_speed=25、attack_power=50、attack_combo_cycle=6、damage_knock_back_count=1、well_distance=0.16
- `e_gom_00501_general_n_Normal_Colorless`: HP=1,000、move_speed=34、attack_power=50、attack_combo_cycle=1、well_distance=0.14
- dungeon系MstInGameの既存レコード: なし（初期データ）
- 最新リリースキー: 202604010（実際のリリース時に更新すること）

## 不確定事項・要確認事項

- release_key: 999999999（仮値。投入するリリースのキーに変更が必要）
- summon_interval の単位: 既存データに合わせて 3（300ms）を使用
- ボスのdrop_battle_point: 既存パラメータと同様の値を採用
