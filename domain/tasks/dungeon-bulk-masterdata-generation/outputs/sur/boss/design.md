# インゲームマスタデータ設計書

## 基本情報

- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）
- シリーズ: 魔都精兵のスレイブ（sur）

## インゲームID命名案

- インゲームID: `dungeon_sur_boss_00001`
- 命名根拠: dungeon_{シリーズ}_{ブロック種別}_{連番5桁} の規則に従いsurシリーズ最初のボスブロック

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（羽前 京香）、雑魚1種（醜鬼無属性） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon boss固定値） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（boss固定） |
| MstAutoPlayerSequence | 3件 | 3行構成（ボス1 + 雑魚2波） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計

- `id`: `dungeon_sur_boss_00001`
- `content_type`: dungeon（限界チャレンジ）
- `stage_type`: dungeon_boss
- `bgm_asset_key`: `SSE_SBG_003_001`（surシリーズ通常バトル共通）
- `loop_background_asset_key`: `sur_00001`
- `normal_enemy_hp_coef`: 1.0（等倍）
- `normal_enemy_attack_coef`: 1.0（等倍）
- `normal_enemy_speed_coef`: 1
- `boss_enemy_hp_coef`: 1.0（等倍）
- `boss_enemy_attack_coef`: 1.0（等倍）
- `boss_enemy_speed_coef`: 1
- `boss_mst_enemy_stage_parameter_id`: `c_sur_00101_general_Boss_Blue`
- `boss_count`: 1

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | color | role |
|--------|------|----------------------|-----|-------|--------|-------|------|
| `c_sur_00101_general_Boss_Blue` | メインボス（羽前 京香） | `chara_sur_00101` | 400,000 | 700 | 45 | Blue | Attack（Boss） |
| `e_sur_00101_general_Normal_Colorless` | 護衛雑魚（醜鬼・無属性） | `enemy_sur_00101` | 3,000 | 100 | 35 | Colorless | Defense（Normal） |

## MstAutoPlayerSequence ウェーブ構成設計

- 総シーケンス行数: 3行
- グループ切り替え: なし（デフォルトグループのみ）

| 行 | condition_type | condition_value | action_value | summon_count | summon_interval | 備考 |
|---|---|---|---|---|---|---|
| 1 | InitialSummon | 0 | `c_sur_00101_general_Boss_Blue` | 1 | — | ボス配置。is_summon_unit_outpost_damage_invalidation=1、summon_position=1.7（砦付近）、aura_type=Boss |
| 2 | ElapsedTime | 20（2,000ms） | `e_sur_00101_general_Normal_Colorless` | 2 | 500 | 護衛醜鬼・第1波（2,000ms後） |
| 3 | ElapsedTime | 45（4,500ms） | `e_sur_00101_general_Normal_Colorless` | 2 | 500 | 護衛醜鬼・第2波（4,500ms後） |

## MstPage / MstKomaLine 構成

- ページID: `dungeon_sur_boss_00001`
- コマ行数: 1行（bossブロック固定）
- コマアセットキー: `sur_00001`
- コマ効果: None（エフェクトなし）
- row=1: height=1.0、koma1_width=1.0（全幅）、layout_asset_key=1

## MstEnemyOutpost 設計

- id: `dungeon_sur_boss_00001`
- hp: 1,000（dungeon bossブロック固定値）
- is_damage_invalidation: 空（ダメージ有効。ボスが生存中の無効化はシーケンス側で制御）
- artwork_asset_key: `sur_00001`

## ステージテキスト（MstInGame I18n）

| 項目 | テキスト |
|---|---|
| result_tips.ja | 誇り高き魔都の剣姫・羽前 京香がボスとして登場。ボスを撃破するまで敵ゲートにダメージが入らないぞ！まずは羽前 京香を倒せ！ |
| description.ja | 魔都最強の剣姫・羽前 京香が立ちはだかる。彼女を打ち倒し、限界チャレンジを突破せよ！ |

## 参照した既存データ

- 参照ステージID: `dungeon_spy_normal_00001`（SPY×FAMILYのdungeon normalブロック）
  - release_key: 999999999（開発テスト用）
  - 同様のboss配置パターン（is_summon_unit_outpost_damage_invalidation=1）を参考に設計
- 参照ステージID: `event_kai1_charaget01_00001`（イベントボスブロックの参照例）
  - aura_type=Boss、InitialSummon配置パターンを参考

## release_key

`999999999`（開発テスト用。投入時に正式なリリースキーへ変更すること）

## 不確定事項・要確認事項

- なし（インゲーム要件テキストに全情報が明記済み）
