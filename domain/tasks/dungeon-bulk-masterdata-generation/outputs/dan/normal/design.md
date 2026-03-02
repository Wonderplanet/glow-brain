# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）

## インゲームID命名案
- インゲームID: `dungeon_dan_normal_00001`
- 命名根拠: dungeon_{シリーズID}_normal_{連番5桁} の命名規則に従い、danシリーズの通常ブロック第1弾

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2種（e_dan_00001_general_n_Normal_Red、e_dan_00101_general_n_Normal_Red） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon通常ブロック固定） |
| MstPage | 1件 | id = dungeon_dan_normal_00001 |
| MstKomaLine | 3件 | 3行構成（dungeon通常ブロック固定） |
| MstAutoPlayerSequence | 6件 | 6行シーケンス、グループ切り替えなし |
| MstInGame | 1件 | id = dungeon_dan_normal_00001 |

## MstInGame 主要パラメータ設計
- `content_type`: dungeon（限界チャレンジ）
- `stage_type`: dungeon_normal
- `bgm_asset_key`: `SSE_SBG_003_001`（ダンダダン通常BGM）
- `boss_bgm_asset_key`: なし（空欄）
- `loop_background_asset_key`: `dan_00007`
- `normal_enemy_hp_coef`: `1`（等倍）
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_mst_enemy_stage_parameter_id`: 空欄（ボスなし）

## MstEnemyStageParameter 敵パラメータ設計

| ID | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | ロール | 属性 |
|-----|------|----------------------|-----|--------|---------|--------|------|
| `e_dan_00001_general_n_Normal_Red` | メイン雑魚（前線維持・壁役） | `enemy_dan_00001` | 10,000 | 50 | 34 | Defense | Red |
| `e_dan_00101_general_n_Normal_Red` | サブ雑魚（高速突破・攻撃役） | `enemy_dan_00101` | 10,000 | 50 | 47 | Attack | Red |

※ 両者とも既存マスタデータに存在確認済み（release_key=202509010）

## MstAutoPlayerSequence ウェーブ構成設計
- 総シーケンス行数: 6行
- グループ切り替え: なし（デフォルトグループのみ）
- トリガー種別: ElapsedTime（経過時間）のみ

| 行 | condition_value | action_value | summon_count |
|----|----------------|--------------|--------------|
| 1 | 350ms | e_dan_00001_general_n_Normal_Red | 1 |
| 2 | 1,000ms | e_dan_00001_general_n_Normal_Red | 2 |
| 3 | 2,500ms | e_dan_00101_general_n_Normal_Red | 2 |
| 4 | 4,000ms | e_dan_00101_general_n_Normal_Red | 3 |
| 5 | 6,000ms | e_dan_00001_general_n_Normal_Red | 1 |
| 6 | 6,500ms | e_dan_00101_general_n_Normal_Red | 2 |

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: 3行（dungeon通常ブロック固定）
- コマ効果: None（全コマ）
- 参照パターン: `normal_dan_00003`（row1: 0.6+0.4、row2: 0.25+0.5+0.25、row3: 0.6+0.4）
- 背景アセット: `dan_00007`

| 行 | layout_asset_key | コマ幅 | 備考 |
|----|-----------------|--------|------|
| row1 | 2 | 0.6 + 0.4 | 2コマ |
| row2 | 9 | 0.25 + 0.5 + 0.25 | 3コマ |
| row3 | 2 | 0.6 + 0.4 | 2コマ |

## MstEnemyOutpost 設計
- `id`: `dungeon_dan_normal_00001`
- `hp`: `100`（dungeon通常ブロック固定）
- `is_damage_invalidation`: 空（ダメージ有効）
- `artwork_asset_key`: `dan_0001`（既存パターンに準じる）

## release_key
- `999999999`（dungeon用リリースキー未確定のため開発テスト用を使用）

## 参照した既存データ
- 参照ステージID: `normal_dan_00003`（コマレイアウト・背景アセット・シーケンスパターン参照）
- 敵パラメータID: `e_dan_00001_general_n_Normal_Red`、`e_dan_00101_general_n_Normal_Red`（既存データ確認済み）

## 不確定事項・要確認事項
- dungeon用の正式リリースキーが未確定のため `999999999` を使用（投入時に変更が必要）
- `content_type` カラムの正確なenum値はschemaで要確認（MstInGame.csv実データ参照推奨）
