# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）
- 対象シリーズ: 姫様"拷問"の時間です（gom）

## インゲームID命名案
- インゲームID: `dungeon_gom_normal_00001`
- 命名根拠: dungeon_{シリーズID}_normal_{連番5桁} の命名規則に従い、gomシリーズの通常ブロック第1弾

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2体（バタートースト・割きトースト） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon_normal固定値） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行固定（dungeon_normal仕様） |
| MstAutoPlayerSequence | 3件 | 3ウェーブ構成 |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `bgm_asset_key`: `SSE_SBG_003_006`（gom専用BGM）
- `loop_background_asset_key`: `gom_00001`
- `player_outpost_asset_key`: `gom_ally_0001`
- `normal_enemy_hp_coef`: `1`（等倍、シーケンス側で調整）
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_mst_enemy_stage_parameter_id`: 空（ボスなし）
- `boss_count`: `0`

## MstEnemyStageParameter 敵パラメータ設計
| 識別子 | 役割 | mst_enemy_character_id | HP基準値 | 攻撃力 | スピード | ロール | 属性 |
|--------|------|------------------------|---------|-------|--------|--------|------|
| `e_gom_00501_gom_dungeon_Normal_Colorless` | メイン雑魚（バタートースト） | `enemy_gom_00501` | 1,000 | 50 | 34 | Defense | Colorless |
| `e_gom_00502_gom_dungeon_Normal_Colorless` | サブ雑魚（割きトースト） | `enemy_gom_00502` | 1,000 | 50 | 34 | Defense | Colorless |

## MstAutoPlayerSequence ウェーブ構成設計

シングルグループ構成（グループ切り替えなし）、全3行。

| 行 | condition_type | condition_value | action_type | action_value | summon_count | summon_interval | enemy_hp_coef | enemy_attack_coef | 備考 |
|----|--------------|----------------|-------------|-------------|-------------|----------------|-------------|-----------------|------|
| 1 | ElapsedTime | 250 | SummonEnemy | e_gom_00502_gom_dungeon_Normal_Colorless | 5 | 300 | 1.5 | 1.5 | 割きトースト×5体、時間差出現 |
| 2 | ElapsedTime | 800 | SummonEnemy | e_gom_00501_gom_dungeon_Normal_Colorless | 5 | 25 | 2.0 | 1.0 | バタートースト×5体、密集出現 |
| 3 | ElapsedTime | 2000 | SummonEnemy | e_gom_00501_gom_dungeon_Normal_Colorless | 5 | 50 | 2.0 | 1.0 | バタートースト×5体、追加ウェーブ |

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: 3行（dungeon_normal固定仕様）
- コマアセット: `gom_00001`（全行・全コマ）
- コマ効果: None（全行・全コマ）

### コマラインレイアウト（SPY normalブロックに準拠）
| row | height | layout | koma1 | koma1_width | koma2 | koma2_width |
|-----|--------|--------|-------|-------------|-------|-------------|
| 1 | 0.55 | 6 | gom_00001 | 0.5 | gom_00001 | 0.5 |
| 2 | 0.55 | 3 | gom_00001 | 0.4 | gom_00001 | 0.6 |
| 3 | 0.55 | 1 | gom_00001 | 1.0 | （なし） | - |

> 注意: 3行のheightが0.55×3=1.65となっているが、SPYの既存データに倣い同値を採用する。

## 参照した既存データ
- 参照ステージID: `dungeon_spy_normal_00001`（SPY×FAMILYのdungeon_normalブロック参考データ）
- 同一構造: MstInGame・MstEnemyOutpost・MstPage・MstKomaLine・MstAutoPlayerSequence の全カラム構成に準拠

## 不確定事項・要確認事項
- `enemy_gom_00501` および `enemy_gom_00502` はMstEnemyCharacterに既存レコードがあることを前提とする
- `gom_ally_0001` アセットキー（player_outpost_asset_key）は要件テキスト記載値を使用
- release_key: `999999999`（開発・テスト用。後で正式リリースキーに変更すること）
