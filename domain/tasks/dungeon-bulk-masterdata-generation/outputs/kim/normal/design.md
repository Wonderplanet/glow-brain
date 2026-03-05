# インゲームマスタデータ設計書

## 基本情報

- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ブロック種別: normal（通常ブロック）
- ステージ種別: dungeon_normal

## インゲームID命名案

- インゲームID: `dungeon_kim_normal_00001`
- 命名根拠: `dungeon_{シリーズ}_normal_{連番5桁}` ルールに従い、シリーズID `kim`（君のことが大大大大大好きな100人の彼女）を使用

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2種類（Colorless・Red） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon_normal固定） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon_normal固定） |
| MstAutoPlayerSequence | 6件 | 6行・ElapsedTimeのみ・グループ切り替えなし |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計

- `content_type`: dungeon
- `stage_type`: dungeon_normal
- `bgm_asset_key`: `SSE_SBG_003_002`（通常バトルBGM）
- `boss_bgm_asset_key`: 空（通常ブロックのためボスBGMなし）
- `loop_background_asset_key`: `kim_00001`（kim専用背景）
- `normal_enemy_hp_coef`: `1`（基本等倍。MstEnemyStageParameterのHP設定で調整）
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_enemy_hp_coef`: `1`
- `boss_enemy_attack_coef`: `1`
- `boss_enemy_speed_coef`: `1`
- `boss_mst_enemy_stage_parameter_id`: 空（通常ブロックのためボスなし）
- `boss_count`: 空
- `release_key`: `999999999`（後で正式リリースキーに変更）

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | 属性 | HP | 攻撃力 | スピード | 索敵距離 | コンボ | ノックバック |
|--------|------|----------------------|------|-----|-------|--------|--------|------|-----------|
| `e_glo_00001_kim1_challenge_Normal_Colorless` | メイン雑魚（無属性） | `enemy_glo_00001` | Colorless | 10,000 | 100 | 40 | 0.25 | 1 | 1 |
| `e_glo_00001_kim1_challenge_Normal_Red` | サブ雑魚（赤属性） | `enemy_glo_00001` | Red | 10,000 | 100 | 40 | 0.25 | 1 | 1 |

**設計根拠:**
- kim専用の雑魚エネミーが存在しないため、GLO汎用エネミー `enemy_glo_00001` を採用
- インゲームID短縮形は `kim1_challenge` を使用（dungeon向けchallenge相当）
- HP: 10,000（dungeon通常ブロック向け・シーケンス等倍なので最終HP = 10,000）
- 攻撃力: 100（dungeon通常ブロック向け・適度な圧力）
- 移動速度: 40（普通〜やや速め）

## MstAutoPlayerSequence ウェーブ構成設計

- 総行数: 6行
- グループ切り替え: なし（デフォルトグループ単一構成）
- トリガー: 全て ElapsedTime

| 行番号 | element_id | condition_type | condition_value | action_value | summon_count | summon_interval | 備考 |
|--------|-----------|---------------|----------------|-------------|-------------|---------------|------|
| 1 | `1` | ElapsedTime | `3`（300ms） | `e_glo_00001_kim1_challenge_Normal_Colorless` | 1 | 0 | 開幕最初の1体 |
| 2 | `2` | ElapsedTime | `10`（1,000ms） | `e_glo_00001_kim1_challenge_Normal_Colorless` | 2 | 500 | 無属性2体追加・500ms間隔 |
| 3 | `3` | ElapsedTime | `20`（2,000ms） | `e_glo_00001_kim1_challenge_Normal_Red` | 1 | 0 | 赤属性初登場 |
| 4 | `4` | ElapsedTime | `30`（3,000ms） | `e_glo_00001_kim1_challenge_Normal_Colorless` | 2 | 500 | 無属性再び |
| 5 | `5` | ElapsedTime | `40`（4,000ms） | `e_glo_00001_kim1_challenge_Normal_Red` | 2 | 600 | 赤属性増量・600ms間隔 |
| 6 | `6` | ElapsedTime | `55`（5,500ms） | `e_glo_00001_kim1_challenge_Normal_Colorless` | 3 | 400 | 終盤の最終波・400ms間隔 |

**補足:** condition_valueはDuckDBで確認した既存データが「100ms単位」であることを確認済み（例: `3` = 300ms）

## MstPage / MstKomaLine 構成

- ページID: `dungeon_kim_normal_00001`（MstInGame.idと同一）
- ページ数: 1
- コマ行数: **3行**（dungeon_normalブロック固定）
- コマ効果: **なし**（全コマ `None`）
- コマ背景アセット: `kim_00001`

| 行 | row | height | layout_key | コマ構成 | 備考 |
|----|-----|--------|-----------|---------|------|
| 1 | 1 | 0.33 | `5` | koma1: 0.6幅 + koma2: 0.4幅 | 2コマ構成 |
| 2 | 2 | 0.34 | `1` | koma1: 1.0幅 | 1コマ構成 |
| 3 | 3 | 0.33 | `6` | koma1: 0.5幅 + koma2: 0.5幅 | 2コマ構成 |

**補足:** height合計 = 0.33 + 0.34 + 0.33 = 1.00

## 参照した既存データ

- DuckDB で `MstInGame.csv` の最新データを参照（release_key最上位: `999999999`, `202604010` 等）
- DuckDB で `MstAutoPlayerSequence.csv` の `plan_test_stage` データを参照し、ElapsedTimeパターンを確認
- DuckDB で `MstKomaLine.csv` のレイアウトパターンを確認（layout_key=5で0.4/0.6、1で1.0、6で0.5/0.5）

## 不確定事項・要確認事項

- `enemy_glo_00001` の実在確認が必要（MstEnemyCharacter.csvに存在するか）
- `kim_00001` 背景アセットキーが実装済みか確認推奨
- release_key: `999999999` を使用（仮値。投入前に正式キーへ変更すること）
