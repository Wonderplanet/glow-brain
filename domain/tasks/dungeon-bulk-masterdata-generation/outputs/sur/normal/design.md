# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）
- 対象シリーズ: 魔都精兵のスレイブ（sur）

## インゲームID命名案
- インゲームID: `dungeon_sur_normal_00001`
- 命名根拠: `dungeon_{シリーズ}_normal_{連番5桁}` の命名規則に従い、surシリーズの通常ブロック第1弾として採番

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 3件 | 雑魚3体（Colorless/Blue/Green属性の醜鬼） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon normal固定値） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon normal固定） |
| MstAutoPlayerSequence | 5件 | 5ウェーブ構成（時間差ElapsedTime） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `content_type`: dungeon（限界チャレンジ）
- `stage_type`: dungeon_normal（通常ブロック）
- `bgm_asset_key`: `SSE_SBG_003_001`（surシリーズ通常バトル共通BGM）
- `loop_background_asset_key`: `sur_00001`
- `normal_enemy_hp_coef`: 1.0（等倍）
- `normal_enemy_attack_coef`: 1.0（等倍）
- `normal_enemy_speed_coef`: 1（等倍）
- `boss_enemy_hp_coef`: 1.0（ボスなし、デフォルト値）
- `boss_enemy_attack_coef`: 1.0
- `boss_enemy_speed_coef`: 1
- `boss_mst_enemy_stage_parameter_id`: 空（ボスなし）
- `boss_count`: 0

## MstEnemyStageParameter 敵パラメータ設計

既存マスタデータに以下のパラメータが存在することをDuckDBで確認済み：

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | 移動速度 | ロール | 属性 |
|--------|------|----------------------|-----|-------|--------|-------|------|
| `e_sur_00101_general_Normal_Colorless` | 序盤前衛雑魚 | enemy_sur_00101 | 3,000 | 100 | 35 | Defense | Colorless |
| `e_sur_00101_general_Normal_Blue` | 中盤主力雑魚 | enemy_sur_00101 | 15,000 | 200 | 40 | Attack | Blue |
| `e_sur_00101_general_Normal_Green` | 終盤強化雑魚 | enemy_sur_00101 | 22,000 | 300 | 50 | Attack | Green |

**注意**: これら3つのパラメータは既にマスタデータに存在するため、新規生成対象外とする。
MstEnemyStageParameter.csv には既存IDの定義を記載するが、実際には既存データを流用する。

## MstAutoPlayerSequence ウェーブ構成設計
- 総ウェーブ数: 5行
- グループ: デフォルトグループのみ（切り替えなし）
- トリガー: すべてElapsedTime（経過時間）

| 行 | トリガー | トリガー値 | エネミーID | 召喚数 | インターバル |
|---|---------|-----------|-----------|-------|------------|
| 1 | ElapsedTime | 300ms（=3 ×100ms） | e_sur_00101_general_Normal_Colorless | 3体 | 200ms |
| 2 | ElapsedTime | 2,000ms（=20） | e_sur_00101_general_Normal_Blue | 2体 | 0ms |
| 3 | ElapsedTime | 3,500ms（=35） | e_sur_00101_general_Normal_Blue | 1体 | 0ms |
| 4 | ElapsedTime | 4,500ms（=45） | e_sur_00101_general_Normal_Green | 1体 | 0ms |
| 5 | ElapsedTime | 5,500ms（=55） | e_sur_00101_general_Normal_Green | 2体 | 500ms |

## MstPage / MstKomaLine 構成
- ページ数: 1（id = `dungeon_sur_normal_00001`）
- コマ行数: 3行（dungeon normal固定）
- コマアセットキー: `sur_00001`
- コマ効果: なし（全行 None）
- SPYのdungeonサンプルを参考にheightは3行均等（各0.55）、koma_line_layout_asset_keyは行構成に応じて設定

### コマライン設計詳細（SPY参考パターンに準拠）
| 行(row) | height | layout_key | コマ構成 |
|---------|--------|-----------|---------|
| 1 | 0.55 | 3 | koma1(0.4) + koma2(0.6) |
| 2 | 0.55 | 9 | koma1(0.25) + koma2(0.5) + koma3(0.25) |
| 3 | 0.55 | 4 | koma1(0.75) + koma2(0.25) |

## 参照した既存データ
- SPY dungeonサンプル: `domain/tasks/masterdata-entry/masterdata-ingame-creator/20260301_131508_dungeon_spy_normal_block/`
- surシリーズ既存敵パラメータ: `projects/glow-masterdata/MstEnemyStageParameter.csv`（3件確認済み）
- リリースキー: `999999999`（開発テスト用。後で正式なリリースキーに変更）

## 不確定事項・要確認事項
- なし（インゲーム要件テキストに基づき全情報が揃っている）
