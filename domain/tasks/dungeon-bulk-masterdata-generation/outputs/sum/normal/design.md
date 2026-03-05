# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）
- シリーズID: sum（サマータイムレンダ）

## インゲームID
- インゲームID: `dungeon_sum_normal_00001`
- 命名根拠: dungeon_{シリーズ}_normal_{連番5桁} 規則に従い、sumシリーズ最初の通常ブロック

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | 雑魚2種（無属性・黄属性の影） — 既存IDを参照 |
| MstEnemyOutpost | 1件 | HP: 100（dungeon_normal固定値） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon_normal固定値） |
| MstAutoPlayerSequence | 5件 | 5行構成（デフォルトグループ単一） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計

- `content_type`: dungeon（限界チャレンジ）
- `stage_type`: dungeon_normal（通常ブロック）— MstInGame.idとして使用
- `bgm_asset_key`: SSE_SBG_003_003（sumシリーズ全コンテンツ共通BGM）
- `boss_bgm_asset_key`: （なし）
- `loop_background_asset_key`: sum_00003（normalシリーズで最多使用の背景）
- `normal_enemy_hp_coef`: 1
- `normal_enemy_attack_coef`: 1
- `normal_enemy_speed_coef`: 1
- `boss_enemy_hp_coef`: 1
- `boss_enemy_attack_coef`: 1
- `boss_enemy_speed_coef`: 1
- `boss_count`: 0（ボスなし）
- `boss_mst_enemy_stage_parameter_id`: （なし）

## MstEnemyStageParameter 敵パラメータ設計（既存ID参照）

| ID | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | 属性 | ロール |
|----|------|----------------------|-----|-------|--------|------|--------|
| e_sum_00001_general_Normal_Colorless | メイン雑魚（序盤〜中盤） | enemy_sum_00001 | 15,000 | 200 | 40 | Colorless | Defense |
| e_sum_00001_general_Normal_Yellow | サブ雑魚（中盤〜終盤） | enemy_sum_00001 | 26,000 | 300 | 40 | Yellow | Defense |

※ 両IDは既存MstEnemyStageParameterに存在するため、新規生成のCSVに含める（同一バッチとしてFK参照可能）

## MstAutoPlayerSequence ウェーブ構成設計

- 総ウェーブ行数: 5行
- グループ: デフォルトグループ単一（グループ切り替えなし）
- シーケンス構成:

| 行 | condition_type | condition_value | action_value | summon_count | 補足 |
|----|----------------|-----------------|--------------|--------------|------|
| 1 | ElapsedTime | 5 (500ms) | e_sum_00001_general_Normal_Colorless | 1 | 序盤・影の登場 |
| 2 | ElapsedTime | 15 (1,500ms) | e_sum_00001_general_Normal_Colorless | 1 | 2体目追加 |
| 3 | ElapsedTime | 25 (2,500ms) | e_sum_00001_general_Normal_Colorless | 2 | 2体まとめ（間隔400ms） |
| 4 | ElapsedTime | 40 (4,000ms) | e_sum_00001_general_Normal_Yellow | 1 | 黄属性の影が登場 |
| 5 | ElapsedTime | 55 (5,500ms) | e_sum_00001_general_Normal_Yellow | 1 | 終盤を締める |

## MstEnemyOutpost 設計

- id: dungeon_sum_normal_00001
- hp: 100（dungeon通常ブロック固定値）
- is_damage_invalidation: （空、ダメージ有効）
- outpost_asset_key: （空）
- artwork_asset_key: sum_00003（背景アセットと同一シリーズ）

## MstPage / MstKomaLine 構成

- ページ数: 1（id: dungeon_sum_normal_00001）
- コマ行数: 3行（dungeon_normal固定値）
- コマ効果: なし（None）
- 背景アセットキー: sum_00003（全行で使用）
- 行構成:
  - row=1: 全幅1コマ（koma_line_layout_asset_key=1、koma1_width=1.0）
  - row=2: 2コマ（koma_line_layout_asset_key=5、koma1_width=0.4、koma2_width=0.6）
  - row=3: 3コマ（koma_line_layout_asset_key=9、koma1_width=0.25、koma2_width=0.5、koma3_width=0.25）

## ステージ説明文・ヒント

- ステージ説明文: 無属性と黄属性の影が次々と押し寄せてくる。黄属性の敵に対しては緑属性のキャラを活用しよう！
- バトルヒント: 影に飲み込まれるな！緑属性のキャラが黄属性の敵に対して有利に戦えるぞ

## release_key

- 999999999（開発・テスト用、後で正式リリースキーに変更）

## 参照した既存データ

- MstEnemyStageParameter: `e_sum_00001_general_Normal_Colorless`（HP:15,000、攻撃力:200、速度:40、Defense）
- MstEnemyStageParameter: `e_sum_00001_general_Normal_Yellow`（HP:26,000、攻撃力:300、速度:40、Defense）
- MstKomaLine参照: `hard_sum_00006_*`（sum_00003背景を使用した3行構成のパターン）

## 不確定事項・要確認事項

- なし（インゲーム要件テキストが承認済みのため、全設定値は確定）
