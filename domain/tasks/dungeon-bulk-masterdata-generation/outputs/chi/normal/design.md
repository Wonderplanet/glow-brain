# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）
- シリーズ: チェンソーマン（chi）

## インゲームID命名案
- インゲームID: `dungeon_chi_normal_00001`
- 命名根拠: dungeon_{シリーズ}_normal_{連番5桁} の命名規則に従う

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2種（Colorless/Yellow） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon通常ブロック固定値） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon通常ブロック固定値） |
| MstAutoPlayerSequence | 5件 | 5ウェーブ構成 |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `content_type`: dungeon
- `stage_type`: dungeon_normal
- `bgm_asset_key`: `SSE_SBG_003_001`（chiシリーズ全コンテンツ共通）
- `loop_background_asset_key`: `glo_00016`（normal_chi_00001/00002/00006で使用実績あり）
- `normal_enemy_hp_coef`: `1`
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_mst_enemy_stage_parameter_id`: 空（bosskなし）
- `boss_count`: 空（ボスなし）

## MstEnemyStageParameter 敵パラメータ設計
| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | 属性 | ロール |
|--------|------|----------------------|-----|-------|--------|------|------|
| `e_chi_00101_general_Normal_Colorless` | 序盤の先遣隊 | `enemy_chi_00101` | 5,000 | 320 | 35 | Colorless | Defense |
| `e_chi_00101_general_Normal_Yellow` | 後半の主力 | `enemy_chi_00101` | 13,000 | 720 | 35 | Yellow | Technical |

※ 両IDとも既存MstEnemyStageParameterに存在することを確認済み（再利用）

## MstAutoPlayerSequence ウェーブ構成設計
- 総ウェーブ数: 5行
- グループ: デフォルトグループのみ（切り替えなし）
- ウェーブ1: ElapsedTime(250ms) → 無属性ゾンビ 1体（開幕の偵察役）
- ウェーブ2: ElapsedTime(600ms) → 無属性ゾンビ 1体（序盤の圧力追加）
- ウェーブ3: ElapsedTime(1200ms) → 黄属性ゾンビ 1体（黄属性の初登場）
- ウェーブ4: ElapsedTime(1600ms) → 黄属性ゾンビ 1体（黄属性の追加）
- ウェーブ5: ElapsedTime(2000ms) → 黄属性ゾンビ 2体・間隔350ms（後半の主力波）

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: 3行（dungeon通常ブロック固定値）
- コマ効果: なし（全コマ None）
- 背景アセット: `glo_00016`
  - row=1: layout=9 (0.25/0.5/0.25の3コマ構成), height=0.55
  - row=2: layout=2 (0.6/0.4の2コマ構成), height=0.55
  - row=3: layout=4 (0.75/0.25の2コマ構成), height=0.55
  ※ heightの合計: 1.65 → dungeon_normalは3行固定のため既存のnormal_chi行高さパターンを参考に設定

## 参照した既存データ
- `normal_chi_00001`: BGM=SSE_SBG_003_001, 背景=glo_00016, 砦HP=50000, coef=全1.0, リリースキー=202509010
- `e_chi_00101_general_Normal_Colorless`: HP=5000, 攻撃=320, 速度=35, Defense（既存IDをそのまま使用）
- `e_chi_00101_general_Normal_Yellow`: HP=13000, 攻撃=720, 速度=35, Technical（既存IDをそのまま使用）
- 最新リリースキー: 999999999（dungeon用に設定）

## 不確定事項・要確認事項
- なし（インゲーム要件テキストに全情報が揃っている）
- MstEnemyStageParameterの2IDは既存データに存在するため新規作成不要
- release_key は 999999999（開発テスト用）を使用する
