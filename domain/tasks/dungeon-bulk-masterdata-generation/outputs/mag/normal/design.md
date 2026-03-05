# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）通常ブロック
- ステージ種別: dungeon_normal
- シリーズ: mag（株式会社マジルミエ）

## インゲームID命名案
- インゲームID: `dungeon_mag_normal_00001`
- 命名根拠: `dungeon_{シリーズ}_normal_{連番5桁}` 規則に従い、magシリーズの通常ブロック第1弾

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2種（冷却系怪異・つらら） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon通常ブロック固定値） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon通常ブロック固定） |
| MstAutoPlayerSequence | 6件 | 6行（ElapsedTime時間差配置） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `id`: `dungeon_mag_normal_00001`
- `bgm_asset_key`: `SSE_SBG_003_001`（マジルミエ通常戦闘BGM）
- `loop_background_asset_key`: `mag_00004`（normalステージ序盤〜中盤で使用）
- `mst_page_id`: `dungeon_mag_normal_00001`
- `mst_enemy_outpost_id`: `dungeon_mag_normal_00001`
- `boss_mst_enemy_stage_parameter_id`: 空（通常ブロックはボスなし）
- `boss_count`: `0`
- `normal_enemy_hp_coef`: `1`
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_enemy_hp_coef`: `1`
- `boss_enemy_attack_coef`: `1`
- `boss_enemy_speed_coef`: `1`
- `release_key`: `999999999`

## MstEnemyStageParameter 敵パラメータ設計
| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | color | character_unit_kind |
|--------|------|----------------------|-----|-------|--------|-------|-------------------|
| `e_mag_00001_general_Normal_Colorless` | メイン雑魚（冷却系怪異） | `enemy_mag_00001` | 70,000 | 1,200 | 35 | Colorless | Normal |
| `e_mag_00101_general_Normal_Blue` | サブ雑魚（つらら） | `enemy_mag_00101` | 10,000 | 1,500 | 100 | Blue | Normal |

### パラメータ設計根拠
- 冷却系怪異（無属性）: HP=70,000・speed=35は中速の攻撃型。dungeon通常ブロックの中核を担う標準雑魚。
- つらら（青属性）: HP=10,000と倒しやすいが速度=100は最速クラス。黄属性が有利な設計。
- 参考: SPY normalブロックでは敵HPをbaseに低い値（1000）を設定してシーケンス倍率で調整するパターンもあるが、本作では設計書通りの実数値を直接設定する。

## MstAutoPlayerSequence ウェーブ構成設計
- 総シーケンス行数: 6行
- グループ切り替え: なし（単一グループ）
- condition_type: ElapsedTime（全行）

| 行 | condition_type | condition_value(ms) | action | 対象パラメータID | count |
|----|---------------|--------------------|----|----------------|-------|
| 1 | ElapsedTime | 500 | SummonEnemy | e_mag_00001_general_Normal_Colorless | 1 |
| 2 | ElapsedTime | 2000 | SummonEnemy | e_mag_00001_general_Normal_Colorless | 1 |
| 3 | ElapsedTime | 4000 | SummonEnemy | e_mag_00001_general_Normal_Colorless | 1 |
| 4 | ElapsedTime | 6000 | SummonEnemy | e_mag_00101_general_Normal_Blue | 1 |
| 5 | ElapsedTime | 6300 | SummonEnemy | e_mag_00101_general_Normal_Blue | 1 |
| 6 | ElapsedTime | 6600 | SummonEnemy | e_mag_00101_general_Normal_Blue | 1 |

## MstPage / MstKomaLine 構成
- ページID: `dungeon_mag_normal_00001`
- コマ行数: 3行（dungeon通常ブロック固定）
- コマ効果: なし（全コマ None）
- 背景アセット: `mag_00004`（全コマ共通）

| row | height | layout | koma1_width | koma2_width | koma3_width |
|-----|--------|--------|-------------|-------------|-------------|
| 1 | 0.55 | 6（2コマ均等割） | 0.5 | 0.5 | - |
| 2 | 0.55 | 3（2コマ0.4/0.6） | 0.4 | 0.6 | - |
| 3 | 0.55 | 1（1コマ全幅） | 1.0 | - | - |

> row 3行でheightの合計が1.65となるが、SPY参考CSVで実績ある0.55×3構成を踏襲する。

## MstEnemyOutpost 設計
- id: `dungeon_mag_normal_00001`
- HP: `100`（dungeon通常ブロック固定値）
- is_damage_invalidation: 空（通常型）
- outpost_asset_key: 空
- artwork_asset_key: 空

## MstInGame 説明文・Tips
- `description.ja`: 「魔法少女たちが怪異を退治するマジルミエの世界へようこそ。青属性の敵も登場するので黄属性のキャラは有利に戦える。後半は超高速のつらら（怪異）が突撃してくるので注意！」
- `result_tips.ja`: 空（バトルヒントは未整備）

## 参照した既存データ
- 参照ステージID: `dungeon_spy_normal_00001`（SPY×FAMILY normalブロック参考）
  - 同じdungeon_normal種別のパターンを踏襲
  - MstInGame: BGM/背景/各coef設定パターン
  - MstKomaLine: 3行構成、各コマレイアウト
  - MstAutoPlayerSequence: ElapsedTimeによる時間差配置
- 最新リリースキー: `999999999`（開発テスト用、投入時に変更）

## 不確定事項・要確認事項
- enemy_mag_00001, enemy_mag_00101 のMstEnemyCharacter登録状況（投入時に確認が必要）
- release_key: 投入予定リリースが確定したら正式キーに変更
- バトルヒント（result_tips.ja）: 未整備のため空欄で作成
