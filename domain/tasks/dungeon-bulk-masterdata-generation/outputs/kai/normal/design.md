# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）

## インゲームID命名案
- インゲームID: `dungeon_kai_normal_00001`
- 命名根拠: `dungeon_{シリーズ}_{ブロック種別}_{連番5桁}` の規則に基づく。シリーズID=kai、ブロック種別=normal、連番=00001。

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 1件 | 雑魚1体（既存IDを再利用） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon_normal固定） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon_normal固定） |
| MstAutoPlayerSequence | 3件 | 3〜4行シーケンス（ElapsedTimeトリガーのみ） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `content_type`: dungeon
- `stage_type`: dungeon_normal
- `bgm_asset_key`: `SSE_SBG_003_001`（kai専用BGM）
- `boss_bgm_asset_key`: なし（通常ブロックのためボスBGMなし）
- `loop_background_asset_key`: `kai_00001`（kai作品専用背景）
- `normal_enemy_hp_coef`: `1`
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_mst_enemy_stage_parameter_id`: なし（通常ブロックのためボスなし）
- `boss_count`: なし

## MstEnemyStageParameter 敵パラメータ設計
| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | ロール | 属性 |
|--------|------|----------------------|----|-------|--------|------|------|
| `e_kai_00101_general_Normal_Colorless` | メイン雑魚 | `enemy_kai_00101` | 25,000 | 350 | 45 | Defense | Colorless |

> 既存IDを使用。新規生成不要。

## MstAutoPlayerSequence ウェーブ構成設計
- 総シーケンス行数: 3行
- グループ切り替え: なし（単一デフォルトグループ）
- トリガー: ElapsedTimeのみ

| 行 | element_id | condition_type | condition_value | action_type | action_value | summon_count |
|----|-----------|---------------|----------------|------------|-------------|-------------|
| 1 | 1 | ElapsedTime | 500（5×100ms） | SummonEnemy | e_kai_00101_general_Normal_Colorless | 1 |
| 2 | 2 | ElapsedTime | 2000（20×100ms） | SummonEnemy | e_kai_00101_general_Normal_Colorless | 1 |
| 3 | 3 | ElapsedTime | 4000（40×100ms） | SummonEnemy | e_kai_00101_general_Normal_Colorless | 1 |

- コメント: バトル開始500ms後に最初の余獣を1体召喚し、2,000ms後・4,000ms後にそれぞれ1体ずつ追加召喚。3体が段階的に前進する構成。シンプルな3シーケンスで完結。

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: 3行（dungeon_normal固定）
- コマ効果: なし（全コマ None）
- コマアセットキー: `kai_00001`（全コマ共通）

| row | height | koma構成 | コマアセット |
|-----|--------|---------|------------|
| 1 | 0.34 | 1コマ（width=1.0、layout=1） | kai_00001 |
| 2 | 0.33 | 1コマ（width=1.0、layout=1） | kai_00001 |
| 3 | 0.33 | 1コマ（width=1.0、layout=1） | kai_00001 |

> heightの合計: 0.34 + 0.33 + 0.33 = 1.0

## MstEnemyOutpost 設計
| 項目 | 値 |
|------|-----|
| id | `dungeon_kai_normal_00001` |
| hp | `100`（dungeon_normal固定値） |
| is_damage_invalidation | 空（ダメージ有効） |
| outpost_asset_key | `kai_00001`（kaiシリーズ共通） |
| artwork_asset_key | `kai_00001`（kaiシリーズ共通） |

## 参照した既存データ
- 参照シリーズ: normal_spy_* （dungeon_normal相当の参考パターンとして参照）
- 既存 `e_kai_00101_general_Normal_Colorless`（HP:25000、攻撃力:350、移動速度:45、Defense/Normal/Colorless）確認済み
- 最新リリースキー: `202604010`（新規投入用に使用）

## 不確定事項・要確認事項
- なし
