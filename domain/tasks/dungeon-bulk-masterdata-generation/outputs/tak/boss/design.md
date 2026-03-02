# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss

## インゲームID命名案
- インゲームID: `dungeon_tak_boss_00001`
- 命名根拠: `dungeon_{シリーズ}_boss_{連番5桁}` の命名規則に従い、takシリーズの1番目のボスブロック

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（タコピー）、雑魚1種（GLO汎用エネミー） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeonボスブロック固定） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（dungeonボスブロック固定） |
| MstAutoPlayerSequence | 3件 | 3行構成（ボス初期配置1行 + 護衛雑魚2波） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `content_type`: dungeon
- `stage_type`: dungeon_boss
- `bgm_asset_key`: `SSE_SBG_003_002`（takシリーズ共通BGM）
- `loop_background_asset_key`: `glo_00004`
- `normal_enemy_hp_coef`: 1（個別シーケンス倍率で調整）
- `normal_enemy_attack_coef`: 1（個別シーケンス倍率で調整）
- `normal_enemy_speed_coef`: 1
- `boss_enemy_hp_coef`: 1（個別シーケンス倍率で調整）
- `boss_enemy_attack_coef`: 1（個別シーケンス倍率で調整）
- `boss_enemy_speed_coef`: 1
- `boss_count`: 1
- `boss_mst_enemy_stage_parameter_id`: `c_tak_00001_tak_dungeon_Boss_Yellow`
- `release_key`: 999999999（開発テスト用）

## MstEnemyStageParameter 敵パラメータ設計
| ID | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | コンボ数 |
|----|------|----------------------|-----|-------|--------|--------|
| `c_tak_00001_tak_dungeon_Boss_Yellow` | ボス（タコピー） | `chara_tak_00001` | 10,000 | 300 | 25 | 5 |
| `e_glo_00001_tak_dungeon_Normal_Colorless` | 護衛雑魚（GLO汎用） | `enemy_glo_00001` | 5,000 | 100 | 34 | 1 |

### 倍率設定（MstAutoPlayerSequenceで調整）
- ボス タコピー: hp_coef=8.0（実効HP 80,000）、atk_coef=2.0（実効攻撃力 600）
- 護衛雑魚: hp_coef=1.5（実効HP 7,500）、atk_coef=3.0（実効攻撃力 300）

## MstAutoPlayerSequence ウェーブ構成設計
- 総シーケンス行数: 3行
- グループ切り替え: なし（デフォルトグループのみ）
- 行1（InitialSummon）: タコピー 1体、砦付近（summon_position=1.7）に初期配置
  - is_summon_unit_outpost_damage_invalidation=1（ボス生存中は敵ゲートへのダメージ無効）
  - aura_type=Boss
- 行2（ElapsedTime=1500ms=15×100ms）: GLO汎用エネミー 2体、間隔500ms（summon_interval=500）
- 行3（ElapsedTime=4000ms=40×100ms）: GLO汎用エネミー 3体、間隔700ms（summon_interval=700）

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: **1行**（dungeonボスブロック固定）
- コマ幅: 1.0（フルワイド）
- コマ効果: None（エフェクトなし）
- コマ背景アセット: `glo_00004`
- koma_line_layout_asset_key: 1（1コマ・幅1.0の標準レイアウト）

## MstEnemyOutpost 設計
- id: `dungeon_tak_boss_00001`
- HP: 1,000（dungeonボスブロック固定）
- is_damage_invalidation: 空（ボス撃破後はダメージ有効になる）
- ボスが生存中のダメージ無効は MstAutoPlayerSequence.is_summon_unit_outpost_damage_invalidation=1 で制御

## 参照した既存データ
- 参照ステージID: `dungeon_spy_normal_00001`（SPY×FAMILYのnormalブロック参考）
- dungeonブロックはまだデータが少ないため、SPY×FAMILYの生成済みCSVを参考に構成

## 不確定事項・要確認事項
- `glo_00004` の背景アセットキーが実際にGLO汎用アセットとして存在するか要確認
- `enemy_glo_00001` の mst_enemy_character_id が正しいか要確認（GLO汎用エネミーのキャラクターID）
- release_key は 999999999（開発テスト用）のため、投入時に正式キーへ変更すること
