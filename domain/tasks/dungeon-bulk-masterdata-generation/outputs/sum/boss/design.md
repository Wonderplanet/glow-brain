# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）

## インゲームID命名案
- インゲームID: `dungeon_sum_boss_00001`
- 命名根拠: `dungeon_{シリーズ}_boss_{連番5桁}` のルールに従い、sum（サマータイムレンダ）の初回ボスブロック

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（c_sum_00101）、雑魚1体（e_sum_00001） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeonボスブロック固定） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（dungeonボスブロック固定） |
| MstAutoPlayerSequence | 3件 | 3行構成（ボス初期配置 + 護衛雑魚2パターン） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `bgm_asset_key`: `SSE_SBG_003_003`（sumシリーズ共通BGM）
- `boss_bgm_asset_key`: 空（ボスBGMなし、通常BGMが継続）
- `loop_background_asset_key`: `sum_00003`（normalシリーズ終盤・最終ステージと同背景）
- `mst_auto_player_sequence_set_id`: `dungeon_sum_boss_00001`
- `mst_page_id`: `dungeon_sum_boss_00001`
- `mst_enemy_outpost_id`: `dungeon_sum_boss_00001`
- `boss_mst_enemy_stage_parameter_id`: `c_sum_00101_general_Boss_Red`
- `boss_count`: `1`
- `normal_enemy_hp_coef`: `1`
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_enemy_hp_coef`: `1`
- `boss_enemy_attack_coef`: `1`
- `boss_enemy_speed_coef`: `1`
- `is_summon_unit_outpost_damage_invalidation`: ボス行で `1`（ボス生存中は敵ゲートへのダメージを無効化）
- `result_tips.ja`: `影のウシオを倒さないと敵ゲートにダメージが入らないぞ！青属性のキャラが赤属性の敵に有利だ`
- `description.ja`: `サマータイムレンダの世界から影のウシオが現れた！強力なボスを倒して先へ進め！赤属性の敵が登場するので青属性のキャラが有利に戦えるぞ`

## MstEnemyStageParameter 敵パラメータ設計
| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | コンボ | 属性 |
|--------|------|----------------------|-----|-------|--------|-------|------|
| c_sum_00101_general_Boss_Red | ボス（影のウシオ 小舟 潮） | chara_sum_00101 | 190,000 | 600 | 40 | 7 | Red |
| e_sum_00001_general_Normal_Colorless | 護衛雑魚（影） | enemy_sum_00001 | 15,000 | 200 | 40 | 1 | Colorless |

## MstAutoPlayerSequence ウェーブ構成設計
- 総行数: 3行
- グループ: デフォルトグループ単一構成（グループ切り替えなし）
- 行1: InitialSummon(0) → ボス（c_sum_00101_general_Boss_Red）×1体、summon_position=1.7（砦前配置）、move_start_condition_type=Damage, is_summon_unit_outpost_damage_invalidation=1
- 行2: ElapsedTime(1500ms=15) → 護衛雑魚（e_sum_00001_general_Normal_Colorless）×2体、summon_interval=500
- 行3: ElapsedTime(3500ms=35) → 護衛雑魚（e_sum_00001_general_Normal_Colorless）×1体

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: 1行（dungeonボスブロック固定）
- コマ効果: None（エフェクトなし）
- koma1_asset_key: `sum_00003`（ループ背景と同アセット）
- koma_line_layout_asset_key: `1`（1コマ全幅）

## MstEnemyOutpost 設計
- id: `dungeon_sum_boss_00001`
- HP: `1000`（dungeonボスブロック固定値）
- is_damage_invalidation: 空（ボス撃破によるダメージ無効化はシーケンス側で制御）

## 参照した既存データ
- 参照パターン: chi/boss の dungeon_chi_boss_00001（同じdungeon bossブロック）
- 参照パターン: 要件テキスト記載の固定値を優先適用

## 不確定事項・要確認事項
- なし（インゲーム要件テキストに全情報が記載済み）
