# インゲームマスタデータ設計書

## 基本情報

- 生成日時: 2026-03-02
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_normal（通常ブロック）
- シリーズ: tak（タコピーの原罪）

## インゲームID

- インゲームID: `dungeon_tak_normal_00001`
- 命名根拠: dungeon_{シリーズ}_{ブロック種別}_{連番5桁} の規則に従いシリーズID `tak` を使用

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 1件 | 雑魚1種類（GLO汎用エネミー） |
| MstEnemyOutpost | 1件 | HP: 100（dungeon通常ブロック固定） |
| MstPage | 1件 | |
| MstKomaLine | 3件 | 3行構成（dungeon通常ブロック固定） |
| MstAutoPlayerSequence | 4件 | 4行シーケンス（ElapsedTime完結型） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計

- `bgm_asset_key`: `SSE_SBG_003_002`（takシリーズ共通BGM）
- `loop_background_asset_key`: `glo_00004`
- `mst_page_id`: `dungeon_tak_normal_00001`
- `mst_enemy_outpost_id`: `dungeon_tak_normal_00001`
- `mst_auto_player_sequence_set_id`: `dungeon_tak_normal_00001`
- `boss_mst_enemy_stage_parameter_id`: 空（normalブロックのためボスなし）
- `boss_count`: 1（デフォルト、実質ボスなし）
- `normal_enemy_hp_coef`: 1
- `normal_enemy_attack_coef`: 1
- `normal_enemy_speed_coef`: 1
- `release_key`: 999999999

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | 備考 |
|--------|------|----------------------|-----|-------|--------|------|
| `e_glo_00001_tak_dungeon_Normal_Colorless` | 雑魚 | `e_glo_00001_general_Normal_Colorless` | 5000 | 250 | 34 | hp_coef=1.3で実効HP6,500、atk=250（base 100×2.5） |

> 注: MstEnemyStageParameter.hp に直接 base HP (5,000) を設定し、シーケンス側でhp_coef=1.3を設定して実効HP=6,500とする。攻撃力は要件テキストにある実効値250をそのままattack_powerに設定する。

## MstAutoPlayerSequence ウェーブ構成設計

- グループ: デフォルトグループのみ（グループ切り替えなし）
- トリガー: ElapsedTime 完結型
- 総シーケンス行数: 4行

| 行 | condition_type | condition_value | action_value | summon_count | summon_interval | hp_coef | 備考 |
|----|---------------|----------------|-------------|-------------|----------------|---------|------|
| 1 | ElapsedTime | 0 | e_glo_00001_tak_dungeon_Normal_Colorless | 1 | 0 | 1.3 | 開幕先行体 |
| 2 | ElapsedTime | 4（400ms） | e_glo_00001_tak_dungeon_Normal_Colorless | 3 | 600 | 1.3 | 第1波 |
| 3 | ElapsedTime | 20（2000ms） | e_glo_00001_tak_dungeon_Normal_Colorless | 5 | 800 | 1.3 | 第2波 |
| 4 | ElapsedTime | 45（4500ms） | e_glo_00001_tak_dungeon_Normal_Colorless | 5 | 800 | 1.3 | 第3波（終盤圧力） |

> condition_value はシステム上 100ms単位なので、400ms=4, 2000ms=20, 4500ms=45

## MstPage / MstKomaLine 構成

- ページ数: 1
- コマ行数: 3行（dungeon通常ブロック固定）
- コマ効果: None（全コマ）
- コマ背景アセット: `glo_00004`（全コマ共通）

| 行 | width構成 | layout_asset_key | 備考 |
|----|-----------|-----------------|------|
| 1 | koma1=0.6, koma2=0.4 | 5 | 2コマ（幅0.6+幅0.4） |
| 2 | koma1=1.0 | 1 | 1コマ（幅1.0） |
| 3 | koma1=0.5, koma2=0.5 | 6 | 2コマ（幅0.5+幅0.5） |

## MstEnemyOutpost

- id: `dungeon_tak_normal_00001`
- HP: 100（dungeon通常ブロック固定）
- is_damage_invalidation: 空（ダメージ有効）
- artwork_asset_key: `glo_00004`

## テキスト

- バトルヒント（result_tips.ja）: 無属性の敵が登場する。敵ゲートを守りながら着実に撃破しよう
- ステージ説明文（description.ja）: タコピーの世界の仲間たちが迫ってくる！無属性の敵をすばやく倒して敵ゲートを守り切れ！

## 参照した既存データ

- 参照データ: `dungeon_spy_normal_00001`（SPY×FAMILY normalブロック参考）
- シーケンス行数: 5行（SPY参考）、今回は4行（要件テキスト指定通り）
- MstKomaLine: 3行構成（dungeon normalブロック固定）

## FK参照チェック

- MstAutoPlayerSequence.action_value → MstEnemyStageParameter.id: `e_glo_00001_tak_dungeon_Normal_Colorless` で一致
- MstInGame.mst_page_id → MstPage.id: `dungeon_tak_normal_00001` で一致
- MstInGame.mst_enemy_outpost_id → MstEnemyOutpost.id: `dungeon_tak_normal_00001` で一致
- MstInGame.mst_auto_player_sequence_set_id → MstAutoPlayerSequence.sequence_set_id: `dungeon_tak_normal_00001` で一致
- MstKomaLine.mst_page_id → MstPage.id: `dungeon_tak_normal_00001` で一致

## 不確定事項・要確認事項

- なし
